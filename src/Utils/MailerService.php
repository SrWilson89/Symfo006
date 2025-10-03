<?php

namespace App\Utils;

use App\Entity\ClienteMailSettings;
use App\Entity\SentEmail;
use App\Entity\User;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Component\Mime\Crypto\DkimOptions;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;

class MailerService
{
    private EntityManagerInterface $em;
    private User $sender;
    private Mailer $mailer;
    private Address $fromAddress;
    private ?Address $replyToAddress = null;
    private string $fromEmail;
    private string $fromName;
    private ClienteMailSettings $settings;
    private ?DkimSigner $dkimSigner = null;

    // Si configuraste DKIM, guardamos sus parámetros
    private ?string $dkimDomain;
    private ?string $dkimSelector;

    public function __construct(
        EntityManagerInterface $em,
        User $sender,
        ClienteMailSettings $settings
    ) {
        $this->em = $em;
        $this->sender = $sender;
        $this->settings = $settings;

        // --- FROM: validar y poner fallback seguro ---
        $effectiveFromEmail = trim((string) ($settings->getFromEmail() ?? ''));
        $effectiveFromName  = trim((string) ($settings->getFromName() ?? ''));

        $isValid = static function (?string $email): bool {
            return is_string($email) && $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        };

        if (!$isValid($effectiveFromEmail)) {
            $senderEmail = method_exists($sender, 'getEmail') ? (string) $sender->getEmail() : '';
            if ($isValid($senderEmail)) {
                $effectiveFromEmail = $senderEmail;
                if ($effectiveFromName === '') {
                    $effectiveFromName = $senderEmail;
                }
            } else {
                $effectiveFromEmail = 'no-reply@localhost';
                if ($effectiveFromName === '') {
                    $effectiveFromName = 'No-Reply';
                }
            }
        }

        try {
            $this->fromAddress = new Address($effectiveFromEmail, $effectiveFromName ?: $effectiveFromEmail);
        } catch (\Throwable $e) {
            $effectiveFromEmail = 'no-reply@localhost';
            $effectiveFromName  = 'No-Reply';
            $this->fromAddress  = new Address($effectiveFromEmail, $effectiveFromName);
        }

        $this->fromEmail = $effectiveFromEmail;
        $this->fromName  = $effectiveFromName ?: $effectiveFromEmail;

        if ($replyTo = $settings->getReplyToEmail()) {
            if ($isValid($replyTo)) {
                $this->replyToAddress = new Address($replyTo, $settings->getFromName() ?? $replyTo);
            }
        }

        // --- DSN: robusto frente a nulls ---
        $encRaw = $settings->getSmtpEncryption();
        $enc = strtolower(trim((string) ($encRaw ?? '')));
        if ($enc === '' || $enc === 'starttls') {
            $enc = 'tls';
        }

        $scheme = match ($enc) {
            'ssl', 'smtps' => 'smtps',
            'tls' => 'smtp',
            default => 'smtp',
        };

        $host = $settings->getSmtpHost() ?: 'localhost';
        $port = $settings->getSmtpPort();
        if ($port === null) {
            $port = match ($scheme) {
                'smtps' => 465,
                default => ($enc === 'tls' ? 587 : 25),
            };
        }

        $user = rawurlencode((string) ($settings->getSmtpUsername() ?? ''));
        $pass = rawurlencode((string) ($settings->getSmtpPassword() ?? ''));

        $dsn = sprintf('%s://%s:%s@%s:%d', $scheme, $user, $pass, $host, (int) $port);
        $query = [];
        $authMode = $settings->getSmtpAuthMode();
        if ($authMode) {
            $query['auth_mode'] = $authMode;
        }
        if ($query) {
            $dsn .= '?' . http_build_query($query);
        }

        try {
            /** @var TransportInterface $transport */
            $transport = Transport::fromDsn($dsn);
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                sprintf('No se pudo crear el transporte SMTP a partir del DSN "%s": %s', $dsn, $e->getMessage()),
                0,
                $e
            );
        }

        $this->mailer = new Mailer($transport);

        $this->dkimDomain   = ($settings->getDkimDomain() ? trim((string) $settings->getDkimDomain()) : null);
        $this->dkimSelector = ($settings->getDkimSelector() ? trim((string) $settings->getDkimSelector()) : null);

        $dkimPrivateKeyPath = $settings->getDkimPrivateKeyPath();
        if ($this->dkimDomain && $this->dkimSelector && $dkimPrivateKeyPath) {
            if (!is_file($dkimPrivateKeyPath)) {
                throw new \InvalidArgumentException(sprintf('No se encontró la clave DKIM en la ruta: %s', $dkimPrivateKeyPath));
            }
            $privateKey = file_get_contents($dkimPrivateKeyPath) ?: '';
            if ($privateKey === '') {
                throw new \RuntimeException('La clave privada DKIM está vacía o no se pudo leer.');
            }
            $this->dkimSigner = new DkimSigner(
                $privateKey,
                $this->dkimDomain,
                $this->dkimSelector,
                new DkimOptions(['relaxed' => true])
            );
        }
    }

    /**
     * Envía un correo y registra el intento en base de datos.
     *
     * @throws \RuntimeException si falla el envío
     */
    public function sendEmail(
        array|string $to,
        string $subject,
        ?string $text = null,
        ?string $html = null,
        array $attachments = [],
        array $cc = [],
        array $bcc = [],
        int $priority = 3,
        ?string $template = null,
        array $context = []
    ): void {
        $email = $template
            ? new TemplatedEmail()
            : new Email();

        $email->from($this->fromAddress)
              ->to(...$this->normalizeAddresses($to))
              ->subject($subject)
              ->priority($priority);

        if ($this->replyToAddress) {
            $email->replyTo($this->replyToAddress);
        }

        if ($text) {
            $email->text($text);
        }
        if ($html) {
            $email->html($html);
        }
        if ($template && $email instanceof TemplatedEmail) {
            $email->htmlTemplate($template)
                  ->context($context);
        }
        if (!empty($cc)) {
            $email->cc(...$this->normalizeAddresses($cc));
        }
        if (!empty($bcc)) {
            $email->bcc(...$this->normalizeAddresses($bcc));
        }
        foreach ($attachments as $attachment) {
            if (is_string($attachment)) {
                $email->attachFromPath($attachment);
            } else {
                $email->addPart($attachment);
            }
        }

        $sentEmail = new SentEmail();
        $sentEmail->setFromEmail($this->fromEmail);
        $sentEmail->setSubject($subject);
        $sentEmail->setToRecipients(json_encode($this->normalizeAddressesToString($to)));
        $sentEmail->setCcRecipients(json_encode($this->normalizeAddressesToString($cc)));
        $sentEmail->setBccRecipients(json_encode($this->normalizeAddressesToString($bcc)));
        $sentEmail->setBodyText($text);
        $sentEmail->setBodyHtml($html);
        $sentEmail->setSender($this->sender);

        try {
            $this->mailer->send($email);
            $sentEmail->setSuccess(true);
            $sentEmail->setErrorMessage(null);
        } catch (\Throwable $e) {
            $sentEmail->setSuccess(false);
            $sentEmail->setErrorMessage($e->getMessage());
        }

        $this->em->persist($sentEmail);
        $this->em->flush();

        if (!$sentEmail->isSuccess()) {
            throw new \RuntimeException('Error enviando correo: ' . $sentEmail->getErrorMessage());
        }
    }

    /**
     * Envía un correo de prueba (no verifica DKIM ni SPF).
     *
     * @param string $to
     * @return bool|string
     */
    public function test(string $to): bool|string
    {
        try {
            $this->sendEmail(
                $to,
                'Correo de prueba - MailerService',
                'Este es un mensaje de prueba para verificar el envío desde el sistema.'
            );
            return true;
        } catch (\Throwable $e) {
            return 'Error enviando correo: ' . $e->getMessage();
        }
    }

    /**
     * Verifica si DKIM está configurado correctamente (presencia de parámetros).
     *
     * @return bool
     */
    public function checkDkim(): bool
    {
        return (!is_null($this->dkimDomain) && !is_null($this->dkimSelector));
    }

    /**
     * Verifica si el dominio del remitente tiene un registro SPF válido.
     *
     * @return bool
     */
    public function checkSpf(): bool
    {
        $parts = explode('@', $this->fromEmail);
        if (count($parts) !== 2) {
            return false;
        }

        $domain = $parts[1];
        $txtRecords = dns_get_record($domain, DNS_TXT);

        if (!$txtRecords || count($txtRecords) === 0) {
            return false;
        }

        foreach ($txtRecords as $rec) {
            if (isset($rec['txt']) && stripos($rec['txt'], 'v=spf1') !== false) {
                return true;
            }
        }

        return false;
    }


    /**
     * (Método simulado) Intenta validar si en el header enviado hay DKIM-Signature.
     *
     * En un entorno real, deberías capturar el email recibido o acceder a logs.
     * Aquí devolvemos true si configuraste DKIM y dkimDomain + dkimSelector no son null.
     */
    private function headerDkimSignaturePresente(): bool
    {
        return (!is_null($this->dkimDomain) && !is_null($this->dkimSelector));
    }

    /**
     * Verifica si hay un registro SPF válido para el dominio del remitente.
     *
     * @param string $fromEmail
     * @return bool
     */
    private function verifySpfRecord(string $fromEmail): bool
    {
        $parts = explode('@', $fromEmail);
        if (count($parts) !== 2) {
            return false;
        }
        $domain = $parts[1];

        // Obtener registros TXT del dominio
        $txtRecords = dns_get_record($domain, DNS_TXT);
        if (!$txtRecords || count($txtRecords) === 0) {
            return false;
        }

        foreach ($txtRecords as $rec) {
            if (isset($rec['txt']) && stripos($rec['txt'], 'v=spf1') !== false) {
                return true;
            }
        }

        return false;
    }

    private function normalizeAddresses(array|string $input): array
    {
        if (is_string($input)) {
            return [new Address($input)];
        }

        $addresses = [];
        foreach ($input as $key => $value) {
            if (is_int($key)) {
                $addresses[] = new Address($value);
            } else {
                $addresses[] = new Address($key, $value);
            }
        }
        return $addresses;
    }

    private function normalizeAddressesToString(array|string $input): array
    {
        if (is_string($input)) {
            return [$input];
        }

        $emails = [];
        foreach ($input as $key => $value) {
            if (is_int($key)) {
                $emails[] = $value;
            } else {
                $emails[] = $key;
            }
        }
        return $emails;
    }

    // -----------------------------------------------------------------
    // DKIM
    // -----------------------------------------------------------------

    /**
     * Genera un par de claves DKIM (RSA) y las asigna al cliente.
     * Si ya existen y $force = false, no regenera.
     *
     * @throws \RuntimeException
     */
    public function ensureDkimKeys(
        ClienteMailSettings $settings,
        bool $force = false,
        string $selector = 'mail',
        int $bits = 2048
    ): void {
        if (!$force && $settings->getDkimPrivateKey() && $settings->getDkimPublicKey()) {
            return;
        }

        $config = [
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => $bits,
        ];

        $res = openssl_pkey_new($config);
        if ($res === false) {
            throw new \RuntimeException('No se pudo generar la clave DKIM (OpenSSL).');
        }

        $privKey = null;
        if (!openssl_pkey_export($res, $privKey) || !$privKey) {
            throw new \RuntimeException('No se pudo exportar la clave privada DKIM.');
        }

        $details = openssl_pkey_get_details($res);
        if ($details === false || empty($details['key'])) {
            throw new \RuntimeException('No se pudo extraer la clave pública DKIM.');
        }

        $publicPem = $details['key'];
        $publicKeyClean = self::pemToDnsTxt($publicPem);

        $settings
            ->setDkimKeyAlgorithm('rsa')
            ->setDkimKeyBits($bits)
            ->setDkimSelector($selector)
            ->setDkimPrivateKey($privKey)
            ->setDkimPublicKey($publicKeyClean)
            ->setMailAuthUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($settings);
        $this->em->flush();
    }

    /**
     * Construye el registro TXT de DKIM para DNS a partir del cliente.
     * @return array{type:string,host:string,value:string}
     */
    public function buildDkimDnsRecord(ClienteMailSettings $settings): array
    {
        $domain = $settings->getMailDomain();
        $selector = $settings->getDkimSelector() ?: 'mail';
        $pub = $settings->getDkimPublicKey();

        if (!$domain || !$pub) {
            throw new \InvalidArgumentException('Faltan datos: dominio o clave pública DKIM no definidos en el cliente.');
        }

        $host = sprintf('%s._domainkey.%s', $selector, $domain);
        $value = sprintf('v=DKIM1; k=rsa; p=%s', $pub);

        return [
            'type'  => 'TXT',
            'host'  => $host,
            'value' => $value,
        ];
    }

    // -----------------------------------------------------------------
    // SPF
    // -----------------------------------------------------------------

    /**
     * Construye y persiste un registro SPF recomendado.
     * @return array{type:string,host:string,value:string}
     */
    public function buildSpfRecord(
        ClienteMailSettings $settings,
        array $include = [],
        array $ipv4 = [],
        array $ipv6 = [],
        string $policy = '~all'
    ): array {
        $domain = $settings->getMailDomain();
        if (!$domain) {
            throw new \InvalidArgumentException('Falta el dominio del cliente.');
        }

        $parts = ['v=spf1'];

        foreach ($include as $inc) {
            $inc = trim((string)$inc);
            if ($inc !== '') {
                $parts[] = 'include:' . $inc;
            }
        }
        foreach ($ipv4 as $ip) {
            $ip = trim((string)$ip);
            if ($ip !== '') {
                $parts[] = 'ip4:' . $ip;
            }
        }
        foreach ($ipv6 as $ip) {
            $ip = trim((string)$ip);
            if ($ip !== '') {
                $parts[] = 'ip6:' . $ip;
            }
        }

        $policy = trim($policy) ?: '~all';
        $parts[] = $policy;

        $txt = implode(' ', $parts);

        $settings
            ->setSpfRecord($txt)
            ->setMailAuthUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($settings);
        $this->em->flush();

        return [
            'type'  => 'TXT',
            'host'  => $domain,
            'value' => $txt,
        ];
    }

    // -----------------------------------------------------------------
    // DMARC
    // -----------------------------------------------------------------

    /**
     * Persiste la configuración DMARC en el cliente.
     */
    public function setDmarcConfig(
        ClienteMailSettings $settings,
        ?string $policy = 'quarantine',       // none | quarantine | reject
        ?string $rua = null,                  // mailto:...
        ?string $ruf = null,                  // mailto:...
        ?string $subdomainPolicy = null,      // none | quarantine | reject
        ?string $adkim = 's',                 // s | r
        ?string $aspf = 's',                  // s | r
        ?int $pct = null                      // 1..100
    ): void {
        if ($policy !== null) {
            $policy = strtolower($policy);
            if (!in_array($policy, ['none', 'quarantine', 'reject'], true)) {
                throw new \InvalidArgumentException('Política DMARC inválida: use none | quarantine | reject.');
            }
        }
        if ($subdomainPolicy !== null) {
            $subdomainPolicy = strtolower($subdomainPolicy);
            if (!in_array($subdomainPolicy, ['none', 'quarantine', 'reject'], true)) {
                throw new \InvalidArgumentException('Política DMARC (subdominios) inválida: use none | quarantine | reject.');
            }
        }
        if ($adkim !== null) {
            $adkim = strtolower($adkim) === 'r' ? 'r' : 's';
        }
        if ($aspf !== null) {
            $aspf = strtolower($aspf) === 'r' ? 'r' : 's';
        }
        if ($pct !== null && ($pct < 1 || $pct > 100)) {
            throw new \InvalidArgumentException('pct debe estar entre 1 y 100.');
        }

        $settings
            ->setDmarcPolicy($policy)
            ->setDmarcRua($rua)
            ->setDmarcRuf($ruf)
            ->setDmarcSubdomainPolicy($subdomainPolicy)
            ->setDmarcAdkim($adkim)
            ->setDmarcAspf($aspf)
            ->setDmarcPct($pct)
            ->setMailAuthUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($settings);
        $this->em->flush();
    }

    /**
     * Construye el registro TXT de DMARC a partir de datos del cliente y/o parámetros.
     *
     * @return array{type:string,host:string,value:string}
     */
    public function buildDmarcRecord(
        ClienteMailSettings $settings,
        ?string $policy = null,
        ?string $rua = null,
        ?string $ruf = null,
        ?string $subdomainPolicy = null,
        ?string $adkim = null,
        ?string $aspf = null,
        ?int $pct = null
    ): array {
        $domain = $settings->getMailDomain();
        if (!$domain) {
            throw new \InvalidArgumentException('Falta el dominio del cliente.');
        }

        // Usa valores almacenados si no se pasan parámetros
        $policy ??= $settings->getDmarcPolicy() ?? 'quarantine';
        $rua ??= $settings->getDmarcRua();
        $ruf ??= $settings->getDmarcRuf();
        $subdomainPolicy ??= $settings->getDmarcSubdomainPolicy();
        $adkim ??= $settings->getDmarcAdkim() ?? 's';
        $aspf ??= $settings->getDmarcAspf() ?? 's';
        $pct ??= $settings->getDmarcPct();

        $validPolicies = ['none', 'quarantine', 'reject'];
        $policy = strtolower($policy);
        if (!in_array($policy, $validPolicies, true)) {
            throw new \InvalidArgumentException('Política DMARC inválida: use none | quarantine | reject.');
        }

        $tags = ['v=DMARC1', 'p=' . $policy];

        if ($subdomainPolicy) {
            $sp = strtolower($subdomainPolicy);
            if (!in_array($sp, $validPolicies, true)) {
                throw new \InvalidArgumentException('Política DMARC para subdominios inválida: use none | quarantine | reject.');
            }
            $tags[] = 'sp=' . $sp;
        }

        $adkim = strtolower($adkim) === 'r' ? 'r' : 's';
        $aspf  = strtolower($aspf)  === 'r' ? 'r' : 's';
        $tags[] = 'adkim=' . $adkim;
        $tags[] = 'aspf=' . $aspf;

        if ($pct !== null) {
            if ($pct < 1 || $pct > 100) {
                throw new \InvalidArgumentException('pct debe estar entre 1 y 100.');
            }
            $tags[] = 'pct=' . $pct;
        }

        // Normaliza direcciones rua/ruf: añade mailto: si falta.
        $normalizeMailto = static function (?string $addr): ?string {
            if ($addr === null || trim($addr) === '') {
                return null;
            }
            $addr = trim($addr);
            if (!str_starts_with(strtolower($addr), 'mailto:')) {
                $addr = 'mailto:' . $addr;
            }
            return $addr;
        };

        $rua = $normalizeMailto($rua);
        $ruf = $normalizeMailto($ruf);

        if ($rua) {
            $tags[] = 'rua=' . $rua;
        }
        if ($ruf) {
            $tags[] = 'ruf=' . $ruf;
        }

        $value = implode('; ', $tags);
        $host  = sprintf('_dmarc.%s', $domain);

        return [
            'type'  => 'TXT',
            'host'  => $host,
            'value' => $value,
        ];
    }

    // -----------------------------------------------------------------
    // Recolección de registros
    // -----------------------------------------------------------------

    /**
     * Devuelve registros DNS (SPF + DKIM) y opcionalmente DMARC.
     *
     * @param array<string,mixed>|null $dmarcOptions Si se pasa, agrega DMARC. Claves: policy, rua, ruf, subdomainPolicy, adkim, aspf, pct
     * @return array<int, array{type:string,host:string,value:string}>
     */
    public function getDnsRecordsForCustomer(ClienteMailSettings $settings, ?array $dmarcOptions = null): array
    {
        $records = [];

        // DKIM
        if ($settings->getDkimPublicKey()) {
            $records[] = $this->buildDkimDnsRecord($settings);
        }

        // SPF
        $domain = $settings->getMailDomain();
        $spf = $settings->getSpfRecord();
        if (!$spf) {
            $records[] = $this->buildSpfRecord($settings);
        } else {
            $records[] = [
                'type'  => 'TXT',
                'host'  => $domain ?? '@',
                'value' => $spf,
            ];
        }

        // DMARC opcional
        if ($dmarcOptions !== null) {
            $records[] = $this->buildDmarcRecord(
                $settings,
                policy: $dmarcOptions['policy'] ?? null,
                rua: $dmarcOptions['rua'] ?? null,
                ruf: $dmarcOptions['ruf'] ?? null,
                subdomainPolicy: $dmarcOptions['subdomainPolicy'] ?? null,
                adkim: $dmarcOptions['adkim'] ?? null,
                aspf: $dmarcOptions['aspf'] ?? null,
                pct: $dmarcOptions['pct'] ?? null,
            );
        }

        return $records;
    }

    // -----------------------------------------------------------------
    // Utilidades
    // -----------------------------------------------------------------

    /**
     * Convierte un PEM público a una cadena válida para DKIM (sin cabeceras ni saltos).
     */
    public static function pemToDnsTxt(string $pem): string
    {
        $lines = preg_split('/\R/', trim($pem)) ?: [];
        $body = array_filter($lines, static function (string $l): bool {
            return !str_starts_with($l, '-----BEGIN') && !str_starts_with($l, '-----END');
        });
        $joined = implode('', array_map('trim', $body));
        return preg_replace('/\s+/', '', $joined) ?? '';
    }

        /**
     * Envía un correo de prueba simple para verificar la configuración.
     */
    public function sendTest(string $toEmail): void
    {
        $email = (new Email())
            ->from($this->fromAddress)
            ->to(new Address($toEmail))
            ->subject('Prueba de SMTP')
            ->text('Este es un correo de prueba para verificar la configuración SMTP.');

        $this->send($email);
    }

    /**
     * Punto único de envío con firma DKIM opcional.
     */
    public function send(Email $email): void
    {
        // Firmar con DKIM si está configurado
        if ($this->dkimSigner) {
            $email = $this->dkimSigner->sign($email);
        }

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            throw new \RuntimeException('Fallo al enviar el correo: ' . $e->getMessage(), 0, $e);
        }
    }
}

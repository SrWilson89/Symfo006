<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'customer_mail_settings')]
class ClienteMailSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'mailSettings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Clientes $cliente = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mailDomain = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $smtpHost = null;

    #[ORM\Column(nullable: true)]
    private ?int $smtpPort = null;

    #[ORM\Column(length: 32, nullable: true)]
    private ?string $smtpEncryption = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $smtpUsername = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $smtpPassword = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $smtpAuthMode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fromEmail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fromName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $replyToEmail = null;

    // ---- SPF ----
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $spfRecord = null;

    // ---- DKIM ----
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dkimDomain = null;

    #[ORM\Column(length: 63, nullable: true, options: ['default' => 'mail'])]
    private ?string $dkimSelector = 'mail';

    #[ORM\Column(length: 16, nullable: true, options: ['default' => 'rsa'])]
    private ?string $dkimKeyAlgorithm = 'rsa';

    #[ORM\Column(nullable: true, options: ['default' => 2048])]
    private ?int $dkimKeyBits = 2048;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $dkimPublicKey = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $dkimPrivateKey = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dkimPrivateKeyPath = null;

    // ---- DMARC ----
    #[ORM\Column(length: 16, nullable: true)]
    private ?string $dmarcPolicy = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dmarcRua = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dmarcRuf = null;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $dmarcSubdomainPolicy = null;

    #[ORM\Column(length: 1, nullable: true)]
    private ?string $dmarcAdkim = null;

    #[ORM\Column(length: 1, nullable: true)]
    private ?string $dmarcAspf = null;

    #[ORM\Column(nullable: true)]
    private ?int $dmarcPct = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $mailAuthUpdatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCliente(): ?Clientes
    {
        return $this->customer;
    }

    public function setCliente(?Clientes $cliente): self
    {
        if ($this->cliente === $cliente) {
            return $this;
        }

        $previous = $this->cliente;
        $this->cliente = $cliente;

        if ($previous !== null) {
            $previous->setMailSettings(null, false);
        }

        if ($cliente !== null) {
            $cliente->setMailSettings($this, false);
        }

        return $this;
    }

    public function getMailDomain(): ?string
    {
        return $this->mailDomain;
    }

    public function setMailDomain(?string $mailDomain): self
    {
        $this->mailDomain = $mailDomain !== null ? trim($mailDomain) : null;

        return $this;
    }

    public function getSmtpHost(): ?string
    {
        return $this->smtpHost;
    }

    public function setSmtpHost(?string $smtpHost): self
    {
        $this->smtpHost = $smtpHost !== null ? trim($smtpHost) : null;

        return $this;
    }

    public function getSmtpPort(): ?int
    {
        return $this->smtpPort;
    }

    public function setSmtpPort(?int $smtpPort): self
    {
        $this->smtpPort = $smtpPort;

        return $this;
    }

    public function getSmtpEncryption(): ?string
    {
        return $this->smtpEncryption;
    }

    public function setSmtpEncryption(?string $smtpEncryption): self
    {
        $this->smtpEncryption = $smtpEncryption !== null ? strtolower(trim($smtpEncryption)) : null;

        return $this;
    }

    public function getSmtpUsername(): ?string
    {
        return $this->smtpUsername;
    }

    public function setSmtpUsername(?string $smtpUsername): self
    {
        $this->smtpUsername = $smtpUsername !== null ? trim($smtpUsername) : null;

        return $this;
    }

    public function getSmtpPassword(): ?string
    {
        return $this->smtpPassword;
    }

    public function setSmtpPassword(?string $smtpPassword): self
    {
        $this->smtpPassword = $smtpPassword;

        return $this;
    }

    public function getSmtpAuthMode(): ?string
    {
        return $this->smtpAuthMode;
    }

    public function setSmtpAuthMode(?string $smtpAuthMode): self
    {
        $this->smtpAuthMode = $smtpAuthMode !== null ? strtolower(trim($smtpAuthMode)) : null;

        return $this;
    }

    public function getFromEmail(): ?string
    {
        return $this->fromEmail;
    }

    public function setFromEmail(?string $fromEmail): self
    {
        $this->fromEmail = $fromEmail !== null ? trim($fromEmail) : null;

        return $this;
    }

    public function getFromName(): ?string
    {
        return $this->fromName;
    }

    public function setFromName(?string $fromName): self
    {
        $name = $fromName !== null ? trim($fromName) : null;
        $this->fromName = $name !== '' ? $name : null;

        return $this;
    }

    public function getReplyToEmail(): ?string
    {
        return $this->replyToEmail;
    }

    public function setReplyToEmail(?string $replyToEmail): self
    {
        $this->replyToEmail = $replyToEmail !== null ? trim($replyToEmail) : null;

        return $this;
    }

    public function getSpfRecord(): ?string
    {
        return $this->spfRecord;
    }

    public function setSpfRecord(?string $spfRecord): self
    {
        $this->spfRecord = $spfRecord;

        return $this;
    }

    public function getDkimDomain(): ?string
    {
        return $this->dkimDomain;
    }

    public function setDkimDomain(?string $dkimDomain): self
    {
        $this->dkimDomain = $dkimDomain !== null ? trim($dkimDomain) : null;

        return $this;
    }

    public function getDkimSelector(): ?string
    {
        return $this->dkimSelector;
    }

    public function setDkimSelector(?string $dkimSelector): self
    {
        $selector = $dkimSelector !== null ? trim($dkimSelector) : null;
        $this->dkimSelector = $selector !== '' ? $selector : 'mail';

        return $this;
    }

    public function getDkimKeyAlgorithm(): ?string
    {
        return $this->dkimKeyAlgorithm;
    }

    public function setDkimKeyAlgorithm(?string $dkimKeyAlgorithm): self
    {
        $this->dkimKeyAlgorithm = $dkimKeyAlgorithm !== null ? strtolower(trim($dkimKeyAlgorithm)) : null;

        return $this;
    }

    public function getDkimKeyBits(): ?int
    {
        return $this->dkimKeyBits;
    }

    public function setDkimKeyBits(?int $dkimKeyBits): self
    {
        $this->dkimKeyBits = $dkimKeyBits;

        return $this;
    }

    public function getDkimPublicKey(): ?string
    {
        return $this->dkimPublicKey;
    }

    public function setDkimPublicKey(?string $dkimPublicKey): self
    {
        $this->dkimPublicKey = $dkimPublicKey;

        return $this;
    }

    public function getDkimPrivateKey(): ?string
    {
        return $this->dkimPrivateKey;
    }

    public function setDkimPrivateKey(?string $dkimPrivateKey): self
    {
        $this->dkimPrivateKey = $dkimPrivateKey;

        return $this;
    }

    public function getDkimPrivateKeyPath(): ?string
    {
        return $this->dkimPrivateKeyPath;
    }

    public function setDkimPrivateKeyPath(?string $dkimPrivateKeyPath): self
    {
        $this->dkimPrivateKeyPath = $dkimPrivateKeyPath !== null ? trim($dkimPrivateKeyPath) : null;

        return $this;
    }

    public function getDmarcPolicy(): ?string
    {
        return $this->dmarcPolicy;
    }

    public function setDmarcPolicy(?string $dmarcPolicy): self
    {
        $this->dmarcPolicy = $dmarcPolicy;

        return $this;
    }

    public function getDmarcRua(): ?string
    {
        return $this->dmarcRua;
    }

    public function setDmarcRua(?string $dmarcRua): self
    {
        $this->dmarcRua = $dmarcRua;

        return $this;
    }

    public function getDmarcRuf(): ?string
    {
        return $this->dmarcRuf;
    }

    public function setDmarcRuf(?string $dmarcRuf): self
    {
        $this->dmarcRuf = $dmarcRuf;

        return $this;
    }

    public function getDmarcSubdomainPolicy(): ?string
    {
        return $this->dmarcSubdomainPolicy;
    }

    public function setDmarcSubdomainPolicy(?string $dmarcSubdomainPolicy): self
    {
        $this->dmarcSubdomainPolicy = $dmarcSubdomainPolicy;

        return $this;
    }

    public function getDmarcAdkim(): ?string
    {
        return $this->dmarcAdkim;
    }

    public function setDmarcAdkim(?string $dmarcAdkim): self
    {
        $this->dmarcAdkim = $dmarcAdkim;

        return $this;
    }

    public function getDmarcAspf(): ?string
    {
        return $this->dmarcAspf;
    }

    public function setDmarcAspf(?string $dmarcAspf): self
    {
        $this->dmarcAspf = $dmarcAspf;

        return $this;
    }

    public function getDmarcPct(): ?int
    {
        return $this->dmarcPct;
    }

    public function setDmarcPct(?int $dmarcPct): self
    {
        $this->dmarcPct = $dmarcPct;

        return $this;
    }

    public function getMailAuthUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->mailAuthUpdatedAt;
    }

    public function setMailAuthUpdatedAt(?\DateTimeImmutable $mailAuthUpdatedAt): self
    {
        $this->mailAuthUpdatedAt = $mailAuthUpdatedAt;

        return $this;
    }
}

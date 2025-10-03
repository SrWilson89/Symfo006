<?php
declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\ClienteMailSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class MailAuthType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // SMTP básico (al estilo Mailjet)
            ->add('smtpHost', TextType::class, [
                'label' => 'Servidor SMTP',
                'required' => true,
                'attr' => ['placeholder' => 'in-v3.mailjet.com'],
                'help' => 'Servidor proporcionado por tu proveedor (Mailjet, Sendgrid, etc.).',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Indica el servidor SMTP.']),
                ],
            ])
            ->add('smtpPort', IntegerType::class, [
                'label' => 'Puerto',
                'required' => true,
                'attr' => ['min' => 1, 'max' => 65535],
                'help' => 'Puertos habituales: 587 (STARTTLS), 465 (SSL/TLS).',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Indica el puerto de conexión.']),
                    new Assert\Range([
                        'min' => 1,
                        'max' => 65535,
                        'notInRangeMessage' => 'Introduce un puerto válido (1-65535).',
                    ]),
                ],
            ])
            ->add('smtpEncryption', ChoiceType::class, [
                'label' => 'Cifrado',
                'required' => false,
                'placeholder' => 'STARTTLS (recomendado)',
                'choices' => [
                    'STARTTLS (recomendado)' => 'tls',
                    'SSL/TLS' => 'ssl',
                    'Sin cifrado' => 'none',
                ],
                'help' => 'Selecciona el tipo de cifrado indicado por tu proveedor.',
            ])
            ->add('smtpAuthMode', ChoiceType::class, [
                'label' => 'Modo de autenticación',
                'required' => false,
                'placeholder' => 'Automático',
                'choices' => [
                    'LOGIN' => 'login',
                    'PLAIN' => 'plain',
                    'CRAM-MD5' => 'cram-md5',
                ],
                'help' => 'Solo cámbialo si tu proveedor lo requiere expresamente.',
            ])
            ->add('smtpUsername', TextType::class, [
                'label' => 'Usuario',
                'required' => true,
                'attr' => ['placeholder' => 'API key o usuario'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Introduce el usuario SMTP.']),
                ],
            ])
            ->add('smtpPassword', PasswordType::class, [
                'label' => 'Contraseña',
                'required' => true,
                'always_empty' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'help' => 'Para Mailjet y servicios similares suele ser la API secret.',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Introduce la contraseña SMTP.']),
                ],
            ])
            ->add('fromEmail', EmailType::class, [
                'label' => 'Email remitente',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Indica la dirección del remitente.']),
                    new Assert\Email(['message' => 'El remitente debe ser un correo válido.']),
                ],
            ])
            ->add('fromName', TextType::class, [
                'label' => 'Nombre remitente',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Introduce el nombre visible en el remitente.']),
                ],
            ])
            ->add('replyToEmail', EmailType::class, [
                'label' => 'Email de respuesta (opcional)',
                'required' => false,
                'constraints' => [
                    new Assert\Email(['message' => 'Introduce un correo válido para la respuesta.']),
                ],
                'help' => 'Si se indica, las respuestas irán a esta dirección.',
            ])

            // Dominio
            ->add('mailDomain', TextType::class, [
                'label' => 'Dominio de envío',
                'required' => true,
                'help' => 'Dominio principal desde el que saldrán los correos (ej.: midominio.com).',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Indica el dominio de correo']),
                ],
            ])

            // DKIM
            ->add('dkimSelector', TextType::class, [
                'label' => 'Selector DKIM',
                'required' => false,
                'empty_data' => 'mail',
                'help' => 'Nombre del selector usado al publicar la clave DKIM en DNS (habitualmente «mail»).',
            ])

            // SPF (no mapeados, se persisten como string completo)
            ->add('spfIncludes', TextType::class, [
                'label' => 'SPF includes (separados por coma o espacio)',
                'required' => false,
                'mapped' => false,
                'help' => 'Otros dominios autorizados para enviar por ti. Úsalos cuando un proveedor te indique que añadas include.',
                'attr' => ['placeholder' => '_spf.google.com _spf.example.com'],
            ])
            ->add('spfIpv4', TextType::class, [
                'label' => 'SPF IPs v4 (coma o espacio)',
                'required' => false,
                'mapped' => false,
                'help' => 'Direcciones IPv4 permitidas para enviar correos directamente.',
                'attr' => ['placeholder' => '203.0.113.10 198.51.100.25'],
            ])
            ->add('spfIpv6', TextType::class, [
                'label' => 'SPF IPs v6 (coma o espacio)',
                'required' => false,
                'mapped' => false,
                'help' => 'Direcciones IPv6 autorizadas para emitir correo en tu nombre.',
                'attr' => ['placeholder' => '2001:db8::1'],
            ])
            ->add('spfPolicy', ChoiceType::class, [
                'label' => 'Política SPF',
                'required' => true,
                'mapped' => false,
                'choices' => [
                    'Permisivo (~all)' => '~all',
                    'Estricto (-all)' => '-all',
                ],
                'help' => 'Determina qué hacer con correos que no cumplan SPF: permitir (~all) o rechazarlos (-all).',
                'empty_data' => '~all',
                'data' => '~all',
            ])

            // DMARC (mapeados a entidad)
            ->add('dmarcPolicy', ChoiceType::class, [
                'label' => 'Política DMARC',
                'required' => false,
                'choices' => [
                    'Solo monitorizar (none)' => 'none',
                    'Cuarentena (quarantine)' => 'quarantine',
                    'Rechazar (reject)' => 'reject',
                ],
                'help' => 'Acción recomendada para los receptores cuando un mensaje falle DMARC.',
                'placeholder' => 'Selecciona una política',
            ])
            ->add('dmarcRua', TextType::class, [
                'label' => 'DMARC RUA (informes agregados)',
                'required' => false,
                'help' => 'Dirección donde recibir resúmenes diarios de cumplimiento DMARC (formato mailto:).',
                'attr' => ['placeholder' => 'mailto:dmarc-reports@tu-dominio.com'],
            ])
            ->add('dmarcRuf', TextType::class, [
                'label' => 'DMARC RUF (informes forenses)',
                'required' => false,
                'help' => 'Correo para recibir informes forenses en caso de fallos (puede dejarse vacío).',
                'attr' => ['placeholder' => 'mailto:dmarc-failures@tu-dominio.com'],
            ])
            ->add('dmarcSubdomainPolicy', ChoiceType::class, [
                'label' => 'Política subdominios (sp=)',
                'required' => false,
                'choices' => [
                    'Sin especificar' => null,
                    'none' => 'none',
                    'quarantine' => 'quarantine',
                    'reject' => 'reject',
                ],
                'help' => 'Permite aplicar una política distinta para subdominios. Si dudas, déjalo vacío.',
                'placeholder' => '—',
            ])
            ->add('dmarcAdkim', ChoiceType::class, [
                'label' => 'Alineamiento DKIM (adkim)',
                'required' => false,
                'choices' => [
                    'Estricto (s)' => 's',
                    'Relajado (r)' => 'r',
                ],
                'help' => 'Define si el dominio DKIM debe coincidir exactamente (s) o permite variaciones (r).',
                'placeholder' => 's',
            ])
            ->add('dmarcAspf', ChoiceType::class, [
                'label' => 'Alineamiento SPF (aspf)',
                'required' => false,
                'choices' => [
                    'Estricto (s)' => 's',
                    'Relajado (r)' => 'r',
                ],
                'help' => 'Equivalente a adkim pero aplicado a SPF.',
                'placeholder' => 's',
            ])
            ->add('dmarcPct', IntegerType::class, [
                'label' => 'Porcentaje aplicación (pct)',
                'required' => false,
                'help' => 'Porcentaje de mensajes a los que se aplica la política DMARC (100 aplica a todos).',
                'attr' => ['min' => 1, 'max' => 100],
            ])

            ->add('testRecipient', EmailType::class, [
                'label' => 'Enviar correo de prueba a',
                'required' => false,
                'mapped' => false,
                'help' => 'Introduce la dirección que recibirá un correo de prueba con la configuración actual.',
                'attr' => ['placeholder' => 'nombre@tu-dominio.com'],
                'constraints' => [
                    new Assert\Email(['message' => 'Introduce un correo válido para la prueba.']),
                ],
            ])

            // Botones
            ->add('generateDkim', SubmitType::class, [
                'label' => 'Generar/Regenerar DKIM',
                'validate' => false,
                'attr' => ['class' => 'btn btn-warning'],
            ])
            ->add('check', SubmitType::class, [
                'label' => 'Comprobar DNS ahora',
                'validate' => false,
                'attr' => ['class' => 'btn btn-secondary'],
            ])
            ->add('sendTest', SubmitType::class, [
                'label' => 'Enviar correo de prueba',
                'validate' => false,
                'attr' => ['class' => 'btn btn-info'],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Guardar configuración',
                'attr' => ['class' => 'btn btn-primary'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ClienteMailSettings::class,
        ]);
    }
}

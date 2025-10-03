<?php

namespace App\Entity;

use App\Repository\SentEmailRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SentEmailRepository::class)]
#[ORM\HasLifecycleCallbacks]
class SentEmail
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $fromEmail;

    #[ORM\Column(type: 'string', length: 255)]
    private string $subject;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $toRecipients = null; // JSON string de emails

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $ccRecipients = null; // JSON string

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $bccRecipients = null; // JSON string

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $bodyText = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $bodyHtml = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $sentAt;

    #[ORM\Column(type: 'boolean')]
    private bool $success;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $errorMessage = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?User $sender = null;

    public function __construct()
    {
        $this->sentAt = new \DateTimeImmutable();
    }

    // Getters y setters aquí...
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function getToRecipients(): ?string
    {
        return $this->toRecipients;
    }

    public function setToRecipients(?string $toRecipients): self
    {
        $this->toRecipients = $toRecipients;
        return $this;
    }

    public function getCcRecipients(): ?string
    {
        return $this->ccRecipients;
    }

    public function setCcRecipients(?string $ccRecipients): self
    {
        $this->ccRecipients = $ccRecipients;
        return $this;
    }

    public function getBccRecipients(): ?string
    {
        return $this->bccRecipients;
    }

    public function setBccRecipients(?string $bccRecipients): self
    {
        $this->bccRecipients = $bccRecipients;
        return $this;
    }

    public function getBodyText(): ?string
    {
        return $this->bodyText;
    }

    public function setBodyText(?string $bodyText): self
    {
        $this->bodyText = $bodyText;
        return $this;
    }

    public function getBodyHtml(): ?string
    {
        return $this->bodyHtml;
    }

    public function setBodyHtml(?string $bodyHtml): self
    {
        $this->bodyHtml = $bodyHtml;
        return $this;
    }

    public function getSentAt(): \DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt(\DateTimeInterface $sentAt): self
    {
        $this->sentAt = $sentAt;
        return $this;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): self
    {
        $this->success = $success;
        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): self
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): static
    {
        $this->sender = $sender;

        return $this;
    }

    public function getFromEmail(): ?string
    {
        return $this->fromEmail;
    }

    public function setFromEmail(string $fromEmail): static
    {
        $this->fromEmail = $fromEmail;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\PresupuestosRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PresupuestosRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Presupuestos
{
    #[ORM\PrePersist]
    public function onPrePersit(): void
    {
        $now = new \DateTime();
        if ($this->fechacreacion === null) {
            $this->modificacion = $now;
        }
        $this->modificacion = $now;
    }
    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        
        $this->modificacion = new \DateTime();
    }
    public function __toString(): string
    {
        return (string) $this->getId(); // o cualquier campo representativo
    }
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $direccion = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $fechacreacion = null;


    #[ORM\Column]
    private ?bool $tipo = null;

    
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $numref = null;

    #[ORM\ManyToOne(inversedBy: 'presupuestos')]
    private ?Detalles $detalle = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $estado = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $modificacion = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(string $direccion): static
    {
        $this->direccion = $direccion;

        return $this;
    }


    public function isTipo(): ?bool
    {
        return $this->tipo;
    }

    public function setTipo(bool $tipo): static
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getNumref(): ?int
    {
        return $this->numref;
    }

    public function setNumref(?int $numref): static
    {
        $this->numref = $numref;

        return $this;
    }

    public function getDetalle(): ?Detalles
    {
        return $this->detalle;
    }

    public function setDetalle(?Detalles $detalle): static
    {
        $this->detalle = $detalle;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(?string $estado): static
    {
        $this->estado = $estado;

        return $this;
    }

    public function getModificacion(): ?\DateTime
    {
        return $this->modificacion;
    }

    public function setModificacion(?\DateTime $modificacion): static
    {
        $this->modificacion = $modificacion;

        return $this;
    }

    public function getFechacreacion(): ?\DateTime
    {
        return $this->fechacreacion;
    }

    public function setFechacreacion(\DateTime $fechacreacion): static
    {
        $this->fechacreacion = $fechacreacion;

        return $this;
    }
}

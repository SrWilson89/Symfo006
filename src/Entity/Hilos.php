<?php

namespace App\Entity;

use App\Repository\HilosRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HilosRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Hilos
{
    #[ORM\PrePersist]
    public function onPrePersit(): void
    {
        $now = new \DateTime();
        if ($this->fecha_creacion === null) {
            $this->modificacion = $now;
        }
        $this->modificacion = $now;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        
        $this->modificacion = new \DateTime();
    }


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'hilos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tareas $tarea = null;

    #[ORM\ManyToOne(inversedBy: 'hilos')]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    private ?User $usuario = null;



    #[ORM\Column(length: 255)]
    private ?string $notas = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $fecha_creacion = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $modificacion = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTarea(): ?Tareas
    {
        return $this->tarea;
    }

    public function setTarea(?Tareas $tarea): static
    {
        $this->tarea = $tarea;

        return $this;
    }

    public function getUsuario(): ?User
    {
        return $this->usuario;
    }

    public function setUsuario(?User $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
    }


    public function getNotas(): ?string
    {
        return $this->notas;
    }

    public function setNotas(string $notas): static
    {
        $this->notas = $notas;

        return $this;
    }

    public function getFechaCreacion(): ?\DateTime
    {
        return $this->fecha_creacion;
    }

    public function setFechaCreacion(?\DateTime $fecha_creacion): static
    {
        $this->fecha_creacion = $fecha_creacion;

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
}

<?php

namespace App\Entity;

use App\Repository\TareasRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TareasRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Tareas
{
    #[ORM\PrePersist]
    public function onPrePersit(): void
    {
        $now = new \DateTime();
        if ($this->Fecha_Creacion === null) {
            $this->Modificacion = $now;
        }
        $this->Modificacion = $now;

        if ($this->usuario && $this->usuario->getCliente()) {
            $this->cliente = $this->usuario->getCliente()->getId();
        }
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        
        $this->Modificacion = new \DateTime();

        if ($this->usuario && $this->usuario->getCliente()) {
            $this->cliente = $this->usuario->getCliente()->getId();
        }
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;



    #[ORM\Column]
    private ?\DateTime $Fecha_Creacion = null;

    #[ORM\Column]
    private ?\DateTime $Modificacion = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Titulo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Notas = null;


    /**
     * @var Collection<int, Hilos>
     */
    #[ORM\OneToMany(targetEntity: Hilos::class, mappedBy: 'tarea', orphanRemoval: true)]
    private Collection $hilos;

    
    #[ORM\ManyToOne(inversedBy: 'tareas')]
    private ?Estados $estado = null;

    #[ORM\ManyToOne(inversedBy: 'tareas')]
    private ?User $usuario = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $fechafin = null;

    #[ORM\Column(nullable: true)]
    private ?int $cliente = null;

    public function __construct()
    {
        $this->hilos = new ArrayCollection();
        $this->usuarios = new ArrayCollection();
        
    }
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getFechaCreacion(): ?\DateTime
    {
        return $this->Fecha_Creacion;
    }
    public function setFechaCreacion(\DateTime $Fecha_Creacion): static
    {
        $this->Fecha_Creacion = $Fecha_Creacion;

        return $this;
    }
    public function getModificacion(): ?\DateTime
    {
        return $this->Modificacion;
    }
    public function setModificacion(\DateTime $Modificacion): static
    {
        $this->Modificacion = $Modificacion;

        return $this;
    }
    public function getTitulo(): ?string
    {
        return $this->Titulo;
    }
    public function setTitulo(?string $Titulo): static
    {
        $this->Titulo = $Titulo;

        return $this;
    }
    public function getNotas(): ?string
    {
        return $this->Notas;
    }
    public function setNotas(?string $Notas): static
    {
        $this->Notas = $Notas;

        return $this;
    }
    /**
     * @return Collection<int, Hilos>
     */
    public function getHilos(): Collection
    {
        return $this->hilos;
    }

    public function addHilo(Hilos $hilo): static
    {
        if (!$this->hilos->contains($hilo)) {
            $this->hilos->add($hilo);
            $hilo->setTarea($this);
        }

        return $this;
    }

    public function removeHilo(Hilos $hilo): static
    {
        if ($this->hilos->removeElement($hilo)) {
            // set the owning side to null (unless already changed)
            if ($hilo->getTarea() === $this) {
                $hilo->setTarea(null);
            }
        }

        return $this;
    }


    public function getEstado(): ?Estados
    {
        return $this->estado;
    }

    public function setEstado(?Estados $estado): static
    {
        $this->estado = $estado;

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

    public function getFechafin(): ?\DateTime
    {
        return $this->fechafin;
    }

    public function setFechafin(?\DateTime $fechafin): static
    {
        $this->fechafin = $fechafin;

        return $this;
    }


    public function getCliente(): ?int
    {
        return $this->cliente;
    }

    public function setCliente(?int $cliente): static
    {
        $this->cliente = $cliente;

        return $this;
    }


    
  
    



}

<?php

namespace App\Entity;

use App\Repository\EstadosRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EstadosRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Estados
{
    public function setId(int $id): ?int
    {
        return $this->id;
    }

    public function __tostring()
    {
        return $this->Nombre;
    }
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Nombre = null;

    #[ORM\Column(length: 255)]
    private ?string $Color = null;

    #[ORM\ManyToOne(inversedBy: 'estados')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Clientes $cliente = null;

    /**
     * @var Collection<int, Tareas>
     */
    #[ORM\OneToMany(targetEntity: Tareas::class, mappedBy: 'estado')]
    private Collection $tareas;

    #[ORM\Column(nullable: true)]
    private ?bool $fin = null;
 
    public function __construct()
    {
        $this->tareas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->Nombre;
    }

    public function setNombre(string $Nombre): static
    {
        $this->Nombre = $Nombre;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->Color;
    }

    public function setColor(string $Color): static
    {
        $this->Color = $Color;

        return $this;
    }

    public function getCliente(): ?Clientes
    {
        return $this->cliente;
    }

    public function setCliente(?Clientes $cliente): static
    {
        $this->cliente = $cliente;

        return $this;
    }

    /**
     * @return Collection<int, Tareas>
     */
    public function getTareas(): Collection
    {
        return $this->tareas;
    }

    public function addTarea(Tareas $tarea): static
    {
        if (!$this->tareas->contains($tarea)) {
            $this->tareas->add($tarea);
            $tarea->setEstado($this);
        }

        return $this;
    }

    public function removeTarea(Tareas $tarea): static
    {
        if ($this->tareas->removeElement($tarea)) {
            // set the owning side to null (unless already changed)
            if ($tarea->getEstado() === $this) {
                $tarea->setEstado(null);
            }
        }

        return $this;
    }

    public function isFin(): ?bool
    {
        return $this->fin;
    }

    public function setFin(?bool $fin): static
    {
        $this->fin = $fin;

        return $this;
    }

    
}

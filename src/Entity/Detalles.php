<?php

namespace App\Entity;

use App\Repository\DetallesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DetallesRepository::class)]
class Detalles
{
    public function __toString(): string
    {
        return (string) $this->getId(); // o cualquier campo representativo
    }
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'detalles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Productos $producto = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $precio = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $iva = null;

    /**
     * @var Collection<int, Presupuestos>
     */
    #[ORM\OneToMany(targetEntity: Presupuestos::class, mappedBy: 'detalle')]
    private Collection $presupuestos;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $cantidad = null;

    public function __construct()
    {
        $this->presupuestos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProducto(): ?Productos
    {
        return $this->producto;
    }

    public function setProducto(?Productos $producto): static
    {
        $this->producto = $producto;

        return $this;
    }

    public function getPrecio(): ?string
    {
        return $this->precio;
    }

    public function setPrecio(string $precio): static
    {
        $this->precio = $precio;

        return $this;
    }

    public function getIva(): ?string
    {
        return $this->iva;
    }

    public function setIva(string $iva): static
    {
        $this->iva = $iva;

        return $this;
    }

    /**
     * @return Collection<int, Presupuestos>
     */
    public function getPresupuestos(): Collection
    {
        return $this->presupuestos;
    }

    public function addPresupuesto(Presupuestos $presupuesto): static
    {
        if (!$this->presupuestos->contains($presupuesto)) {
            $this->presupuestos->add($presupuesto);
            $presupuesto->setDetalle($this);
        }

        return $this;
    }

    public function removePresupuesto(Presupuestos $presupuesto): static
    {
        if ($this->presupuestos->removeElement($presupuesto)) {
            // set the owning side to null (unless already changed)
            if ($presupuesto->getDetalle() === $this) {
                $presupuesto->setDetalle(null);
            }
        }

        return $this;
    }

    public function getCantidad(): ?float
    {
        return $this->cantidad;
    }

    public function setCantidad(?float $cantidad): static
    {
        $this->cantidad = $cantidad;

        return $this;
    }
}

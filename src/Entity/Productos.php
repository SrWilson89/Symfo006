<?php

namespace App\Entity;

use App\Repository\ProductosRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductosRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Productos
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
        // Retorna una propiedad representativa del producto, como el nombre
        return $this->nombre ?? 'Producto sin nombre';
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nombre = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $precio = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $iva = null;

    #[ORM\Column]
    private ?\DateTime $fechacreacion = null;

    #[ORM\Column]
    private ?\DateTime $modificacion = null;

    /**
     * @var Collection<int, Detalles>
     */
    #[ORM\OneToMany(targetEntity: Detalles::class, mappedBy: 'producto')]
    private Collection $detalles;

    public function __construct()
    {
        $this->detalles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(?string $nombre): static
    {
        $this->nombre = $nombre;

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

    public function getFechacreacion(): ?\DateTime
    {
        return $this->fechacreacion;
    }

    public function setFechacreacion(\DateTime $fechacreacion): static
    {
        $this->fechacreacion = $fechacreacion;

        return $this;
    }

    public function getModificacion(): ?\DateTime
    {
        return $this->modificacion;
    }

    public function setModificacion(\DateTime $modificacion): static
    {
        $this->modificacion = $modificacion;

        return $this;
    }

    /**
     * @return Collection<int, Detalles>
     */
    public function getDetalles(): Collection
    {
        return $this->detalles;
    }

    public function addDetalle(Detalles $detalle): static
    {
        if (!$this->detalles->contains($detalle)) {
            $this->detalles->add($detalle);
            $detalle->setProducto($this);
        }

        return $this;
    }

    public function removeDetalle(Detalles $detalle): static
    {
        if ($this->detalles->removeElement($detalle)) {
            // set the owning side to null (unless already changed)
            if ($detalle->getProducto() === $this) {
                $detalle->setProducto(null);
            }
        }

        return $this;
    }
}

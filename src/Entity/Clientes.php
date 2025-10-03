<?php

namespace App\Entity;

use App\Repository\ClientesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientesRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Clientes
{
    #[ORM\PrePersist]
    public function onPrePersit(): void
    {
        $now = new \DateTime();
        if ($this->Fech_creacion === null) {
            $this->Fech_creacion = $now;
            $plus30 = new \DateTime();
            $plus30->add(new \DateInterval('P30D'));
            $this->testat = $plus30;
        }
        $this->modificacion = $now;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        
        $this->modificacion = new \DateTime();
    }

    public function setId(int $id): ?int
    {
        return $this->id;
    }

     public function __tostring()
    {
        return $this->nombre;
    }

    public function checking()
    {
        $now = new \DateTime();
        if ($this->activo == true && $now < $this->testat){
            return true;
        }
        return false;
    }
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    private ?string $direccion = null;

    #[ORM\Column(length: 6)]
    private ?string $codigo_postal = null;

    #[ORM\Column(length: 255)]
    private ?string $pais = null;

    #[ORM\Column(length: 255)]
    private ?string $provincia = null;

    #[ORM\Column(length: 255)]
    private ?string $localidad = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $notas = null;

    #[ORM\Column(length: 255)]
    private ?string $cif = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $Fech_creacion = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $modificacion = null;

    #[ORM\Column(nullable: true)]
    private ?bool $activo = null;
    #[ORM\OneToOne(mappedBy: 'cliente', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?ClienteMailSettings $mailSettings = null;

    /**
     * @var Collection<int, Estados>
     */
    #[ORM\OneToMany(targetEntity: Estados::class, mappedBy: 'cliente')]
    private Collection $estados;

    /**
     * @var Collection<int, Tareas>
     */
    #[ORM\OneToMany(targetEntity: Tareas::class, mappedBy: 'cliente')]
    private Collection $tareas;

    /**
     * @var Collection<int, Usuarios>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'cliente')]
    private Collection $usuarios;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $testat = null;


    public function __construct()
    {
        $this->estados = new ArrayCollection();
        $this->tareas = new ArrayCollection();
        $this->usuarios = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
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

    public function getCodigoPostal(): ?string
    {
        return $this->codigo_postal;
    }

    public function setCodigoPostal(string $codigo_postal): static
    {
        $this->codigo_postal = $codigo_postal;

        return $this;
    }

    public function getPais(): ?string
    {
        return $this->pais;
    }

    public function setPais(string $pais): static
    {
        $this->pais = $pais;

        return $this;
    }

    public function getLocalidad(): ?string
    {
        return $this->localidad;
    }

    public function setLocalidad(string $localidad): static
    {
        $this->localidad = $localidad;

        return $this;
    }

    public function getNotas(): ?string
    {
        return $this->notas;
    }

    public function setNotas(?string $notas): static
    {
        $this->notas = $notas;

        return $this;
    }

    public function getCif(): ?string
    {
        return $this->cif;
    }

    public function setCif(string $cif): static
    {
        $this->cif = $cif;

        return $this;
    }

    public function getFechCreacion(): ?\DateTime
    {
        return $this->Fech_creacion;
    }

    public function setFechCreacion(\DateTime $Fech_creacion): static
    {
        $this->Fech_creacion = $Fech_creacion;

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

    public function getProvincia(): ?string
    {
        return $this->provincia;
    }

    public function setProvincia(string $provincia): static
    {
        $this->provincia = $provincia;

        return $this;
    }

    public function isActivo(): ?bool
    {
        return $this->activo;
    }

    public function setActivo(?bool $activo): static
    {
        if ($activo == false)
        {
            foreach ($this->usuarios as $usuario){
                $usuario->setActivo(false);
            }
        }
        $this->activo = $activo;

        return $this;
    }

    /**
     * @return Collection<int, Estados>
     */
    public function getEstados(): Collection
    {
        return $this->estados;
    }

    public function addEstado(Estados $estado): static
    {
        if (!$this->estados->contains($estado)) {
            $this->estados->add($estado);
            $estado->setCliente($this);
        }

        return $this;
    }

    public function removeEstado(Estados $estado): static
    {
        if ($this->estados->removeElement($estado)) {
            // set the owning side to null (unless already changed)
            if ($estado->getCliente() === $this) {
                $estado->setCliente(null);
            }
        }

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
            $tarea->setCliente($this);
        }

        return $this;
    }

    public function removeTarea(Tareas $tarea): static
    {
        if ($this->tareas->removeElement($tarea)) {
            // set the owning side to null (unless already changed)
            if ($tarea->getCliente() === $this) {
                $tarea->setCliente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Usuarios>
     */
    public function getUsuarios(): Collection
    {
        return $this->usuarios;
    }

    public function addUsuario(User $usuario): static
    {
        if (!$this->usuarios->contains($usuario)) {
            $this->usuarios->add($usuario);
            $usuario->setCliente($this);
        }

        return $this;
    }

    public function removeUsuario(User $usuario): static
    {
        if ($this->usuarios->removeElement($usuario)) {
            // set the owning side to null (unless already changed)
            if ($usuario->getCliente() === $this) {
                $usuario->setCliente(null);
            }
        }

        return $this;
    }

    public function getTestat(): ?\DateTime
    {
        return $this->testat;
    }

    public function setTestat(?\DateTime $testat): static
    {
        $this->testat = $testat;

        return $this;
    }
    // -----------------------------------------------------------------
    // Configuración de correo delegada en ClienteMailSettings
    // -----------------------------------------------------------------
    public function getMailSettings(bool $createIfMissing = false): ?ClienteMailSettings
    {
        if ($createIfMissing && $this->mailSettings === null) {
            $settings = new ClienteMailSettings();
            $settings->setCliente($this);
        }

        return $this->mailSettings;
    }

    public function setMailSettings(?ClienteMailSettings $mailSettings, bool $sync = true): self
    {
        $current = $this->mailSettings;
        if ($current === $mailSettings) {
            return $this;
        }

        $this->mailSettings = $mailSettings;

        if ($sync) {
            if ($mailSettings === null && $current !== null) {
                $current->setCliente(null);
            } elseif ($mailSettings !== null && $mailSettings->getCliente() !== $this) {
                $mailSettings->setCliente($this);
            }
        }

        return $this;
    }

    private function provideMailSettings(): ClienteMailSettings
    {
        $settings = $this->getMailSettings(true);
        \assert($settings instanceof ClienteMailSettings);

        return $settings;
    }

    public function getMailDomain(): ?string
    {
        return $this->mailSettings?->getMailDomain();
    }

    public function setMailDomain(?string $mailDomain): self
    {
        $this->provideMailSettings()->setMailDomain($mailDomain);

        return $this;
    }

    
}

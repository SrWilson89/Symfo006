<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
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

    public function setId(int $id): ?int
    {
        return $this->id;
    }

    public function __tostring()
    {
        return $this->nombre;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function eraseCredentials(): void {}

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $apellidos = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $nif = null;


    #[ORM\Column(length: 9, nullable: true)]
    private ?string $telefono = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $fecha_creacion = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $modificacion = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email;

    #[ORM\Column(length: 255 )]
    private ?string $password;

    #[ORM\Column]
    private ?int $super = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $notas = null;

    #[ORM\Column(nullable: true)]
    private ?bool $activo = null;


    #[ORM\ManyToOne(inversedBy: 'usuarios')]
    #[ORM\JoinColumn(nullable: true)]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    private ?Clientes $cliente = null;

    
    #[ORM\Column(length:255, nullable:true)]
    private ?string $imagen;

    /**
     * @var Collection<int, Hilos>
     */
    #[ORM\OneToMany(targetEntity: Hilos::class, mappedBy: 'usuario')]
    private Collection $hilos;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column()]
    private bool $isOnline = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $lastLogin = null;

    /**
     * @var Collection<int, Tareas>
     */
    #[ORM\OneToMany(targetEntity: Tareas::class, mappedBy: 'usuario')]
    private Collection $tareas;



    public function __construct()
    {
        $this->hilos = new ArrayCollection();
        $this->tareas = new ArrayCollection();
        
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

    public function getApellidos(): ?string
    {
        return $this->apellidos;
    }

    public function setApellidos(?string $apellidos): static
    {
        $this->apellidos = $apellidos;

        return $this;
    }

    public function getNif(): ?string
    {
        return $this->nif;
    }

    public function setNif(?string $nif): static
    {
        $this->nif = $nif;

        return $this;
    }


    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(?string $telefono): static
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getFechaCreacion(): ?\DateTime
    {
        return $this->fecha_creacion;
    }

    public function setFechaCreacion(\DateTime $fecha_creacion): static
    {
        $this->fecha_creacion = $fecha_creacion;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

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

    public function isActivo(): ?bool
    {
        return $this->activo;
    }

    public function setActivo(?bool $activo): static
    {
        $this->activo = $activo;

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
            $hilo->setUsuario($this);
        }

        return $this;
    }

    public function removeHilo(Hilos $hilo): static
    {
        if ($this->hilos->removeElement($hilo)) {
            // set the owning side to null (unless already changed)
            if ($hilo->getUsuario() === $this) {
                $hilo->setUsuario(null);
            }
        }

        return $this;
    }

    public function getSuper(): ?int
    {
        return $this->super;
    }

    public function setSuper(int $super): static
    {
        $this->super = $super;

        return $this;
    }

    public function isOnline(): ?bool
    {
        return $this->isOnline;
    }

    public function setIsOnline(bool $isOnline): static
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    public function getLastLogin(): ?\DateTime
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTime $lastLogin): static
    {
        $this->lastLogin = $lastLogin;

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
            $tarea->setUsuario($this);
        }

        return $this;
    }

    public function removeTarea(Tareas $tarea): static
    {
        if ($this->tareas->removeElement($tarea)) {
            // set the owning side to null (unless already changed)
            if ($tarea->getUsuario() === $this) {
                $tarea->setUsuario(null);
            }
        }

        return $this;
    }

    public function getImagen(): ?string
    {
        return $this->imagen;
    }

    public function setImagen(?string $imagen): self
    {
        $this->imagen = $imagen;

        return $this;
    }
   

    
}

<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private $pseudo;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column(type: 'string')]
    private $password;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $nom;

    #[ORM\Column(type: 'string', length: 255)]
    private $prenom;

    #[ORM\Column(type: 'datetime')]
    private $dateDeNaissance;

    #[ORM\OneToOne(targetEntity: self::class, cascade: ['persist', 'remove'])]
    private $utilisateurTire;

    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'utilisateursNonTirants')]
    private $utilisateursInterdits;

    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'utilisateursInterdits')]
    private $utilisateursNonTirants;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Souhait::class, orphanRemoval: true)]
    private $souhaits;

    public function __construct()
    {
        $this->utilisateursInterdits = new ArrayCollection();
        $this->utilisateursNonTirants = new ArrayCollection();
        $this->souhaits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->pseudo;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getDateDeNaissance(): ?\DateTimeInterface
    {
        return $this->dateDeNaissance;
    }

    public function setDateDeNaissance(\DateTimeInterface $dateDeNaissance): self
    {
        $this->dateDeNaissance = $dateDeNaissance;

        return $this;
    }

    public function getUtilisateurTire(): ?self
    {
        return $this->utilisateurTire;
    }

    public function setUtilisateurTire(?self $utilisateurTire): self
    {
        $this->utilisateurTire = $utilisateurTire;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getUtilisateursInterdits(): Collection
    {
        return $this->utilisateursInterdits;
    }

    public function addUtilisateursInterdit(self $utilisateursInterdit): self
    {
        if (!$this->utilisateursInterdits->contains($utilisateursInterdit)) {
            $this->utilisateursInterdits[] = $utilisateursInterdit;
        }

        return $this;
    }

    public function removeUtilisateursInterdit(self $utilisateursInterdit): self
    {
        $this->utilisateursInterdits->removeElement($utilisateursInterdit);

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getUtilisateursNonTirants(): Collection
    {
        return $this->utilisateursNonTirants;
    }

    public function addUtilisateursNonTirant(self $utilisateursNonTirant): self
    {
        if (!$this->utilisateursNonTirants->contains($utilisateursNonTirant)) {
            $this->utilisateursNonTirants[] = $utilisateursNonTirant;
            $utilisateursNonTirant->addUtilisateursInterdit($this);
        }

        return $this;
    }

    public function removeUtilisateursNonTirant(self $utilisateursNonTirant): self
    {
        if ($this->utilisateursNonTirants->removeElement($utilisateursNonTirant)) {
            $utilisateursNonTirant->removeUtilisateursInterdit($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Souhait>
     */
    public function getSouhaits(): Collection
    {
        return $this->souhaits;
    }

    public function addSouhait(Souhait $souhait): self
    {
        if (!$this->souhaits->contains($souhait)) {
            $this->souhaits[] = $souhait;
            $souhait->setUtilisateur($this);
        }

        return $this;
    }

    public function removeSouhait(Souhait $souhait): self
    {
        if ($this->souhaits->removeElement($souhait)) {
            // set the owning side to null (unless already changed)
            if ($souhait->getUtilisateur() === $this) {
                $souhait->setUtilisateur(null);
            }
        }

        return $this;
    }
}

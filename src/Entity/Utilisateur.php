<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\Cascade;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[UniqueEntity(fields: ['pseudo'], message: 'There is already an account with this pseudo')]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{

    public const USER = 'ROLE_USER';
    public const ADMIN = 'ROLE_ADMIN';
    public const PARTICIPANT = 'ROLE_PARTICIPANT';
    public const SPECTATEUR = 'ROLE_SPECTATEUR';

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

    #[ORM\OneToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private $utilisateurTire;

    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'utilisateursNonTirants')]
    private $utilisateursInterdits;

    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'utilisateursInterdits')]
    private $utilisateursNonTirants;

    #[ORM\OneToMany(mappedBy: 'destinataire', targetEntity: Souhait::class, orphanRemoval: true)]
    private $souhaits;

    #[ORM\OneToMany(mappedBy: 'demandeur', targetEntity: Echange::class, orphanRemoval: true)]
    private $demandesFaites;

    #[ORM\OneToMany(mappedBy: 'receveur', targetEntity: Echange::class, orphanRemoval: true)]
    private $demandesRecues;

    public function __construct()
    {
        $this->utilisateursInterdits = new ArrayCollection();
        $this->utilisateursNonTirants = new ArrayCollection();
        $this->souhaits = new ArrayCollection();
        $this->demandesFaitesFaites = new ArrayCollection();
        $this->demandesRecues = new ArrayCollection();
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
        $this->addUtilisateursInterdit($this);
        return $this->utilisateursInterdits;
    }

    public function addUtilisateursInterdit(self $utilisateursInterdit): self
    {
        if (!$this->utilisateursInterdits->contains($utilisateursInterdit)) {
            $this->utilisateursInterdits[] = $utilisateursInterdit;
        }

        if (!$utilisateursInterdit->utilisateursInterdits->contains($this)) {
            $utilisateursInterdit->utilisateursInterdits[] = $this;
        }
        return $this;
    }

    public function removeUtilisateursInterdit(self $utilisateursInterdit): self
    {
        $this->utilisateursInterdits->removeElement($utilisateursInterdit);
        $utilisateursInterdit->utilisateursInterdits->removeElement($this);

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
            $souhait->setDestinataire($this);
        }

        return $this;
    }

    public function removeSouhait(Souhait $souhait): self
    {
        if ($this->souhaits->removeElement($souhait)) {
            // set the owning side to null (unless already changed)
            if ($souhait->getDestinataire() === $this) {
                $souhait->setDestinataire(null);
            }
        }

        return $this;
    }

    public function isTiragePossible(ArrayCollection $utilisateurs): bool
    {
        $id_possible = [];
        foreach ($utilisateurs as $utilisateur) {
            $id_possible[] = $utilisateur->getId();
        }

        if (in_array($this->getId(), $id_possible)) {
            array_splice($id_possible, array_search($this->getId(), $id_possible), 1);
        }

        foreach ($this->getUtilisateursInterdits() as $interdit) {
            if (in_array($interdit->getId(), $id_possible)) {
                array_splice($id_possible, array_search($interdit->getId(), $id_possible), 1);
            }
        }

        return !empty($id_possible);
    }

    public function tire(ArrayCollection $utilisateurs): Utilisateur
    {
        $id_possible = [];
        $utilisateurs_id = [];
        foreach ($utilisateurs as $utilisateur) {
            $utilisateurs_id[$utilisateur->getId()] = $utilisateur;
            $id_possible[] = $utilisateur->getId();
        }

        if (in_array($this->getId(), $id_possible)) {
            array_splice($id_possible, array_search($this->getId(), $id_possible), 1);
        }

        foreach ($this->getUtilisateursInterdits() as $interdit) {
            if (in_array($interdit->getId(), $id_possible)) {
                array_splice($id_possible, array_search($interdit->getId(), $id_possible), 1);
            }
        }
        $u = array_rand($id_possible);
        return $utilisateurs_id[$id_possible[$u]];
    }

    public function __toString()
    {
        return $this->getPrenom();
    }

    /**
     * @return Collection<int, Echange>
     */
    public function getDemandesFaites(): Collection
    {
        return $this->demandesFaites;
    }

    public function addDemandesFaite(Echange $demandesFaite): self
    {
        if (!$this->demandesFaites->contains($demandesFaite)) {
            $this->demandesFaites[] = $demandesFaite;
            $demandesFaite->setDemandeur($this);
        }

        return $this;
    }

    public function removeDemandesFaite(Echange $demandesFaite): self
    {
        if ($this->demandesFaites->removeElement($demandesFaite)) {
            // set the owning side to null (unless already changed)
            if ($demandesFaite->getDemandeur() === $this) {
                $demandesFaite->setDemandeur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Echange>
     */
    public function getDemandesRecues(): Collection
    {
        return $this->demandesRecues;
    }

    public function addDemandesRecue(Echange $demandesRecue): self
    {
        if (!$this->demandesRecues->contains($demandesRecue)) {
            $this->demandesRecues[] = $demandesRecue;
            $demandesRecue->setReceveur($this);
        }

        return $this;
    }

    public function removeDemandesRecue(Echange $demandesRecue): self
    {
        if ($this->demandesRecues->removeElement($demandesRecue)) {
            // set the owning side to null (unless already changed)
            if ($demandesRecue->getReceveur() === $this) {
                $demandesRecue->setReceveur(null);
            }
        }

        return $this;
    }
}

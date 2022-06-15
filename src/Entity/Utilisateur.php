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
    public const NOT_ACTIVE = 'ROLE_NOT_ACTIVE';

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

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
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

    public function addRole(string $role): self
    {
        if (!in_array($role, $this->roles)) {
            if ($role == self::SPECTATEUR) {
                $this->removeRole(self::PARTICIPANT);
            } elseif ($role == self::PARTICIPANT) {
                $this->removeRole(self::SPECTATEUR);
            }
            if ($role == Utilisateur::NOT_ACTIVE) {
                $this->setRoles([$role]);
                return $this;
            } else {
                $this->removeRole(Utilisateur::NOT_ACTIVE);
            }

            $this->roles[] = $role;
        }
        return $this;
    }

    public function removeRole(string $role): self
    {
        if (in_array($role, $this->roles)) {
            $key = array_search($role, $this->roles);
            unset($this->roles[$key]);
        }

        return $this;
    }

    public function toggleRole(string $role): self
    {
        if (in_array($role, $this->roles)) {
            $this->removeRole($role);
        } else {
            $this->addRole($role);
        }
        if (empty($this->roles)) {
            $this->addRole(self::NOT_ACTIVE);
        }
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
        return $this;
    }

    public function removeUtilisateursInterdit(self $utilisateursInterdit): self
    {
        $this->utilisateursInterdits->removeElement($utilisateursInterdit);
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
            if ($this != $utilisateur) {
                $id_possible[] = $utilisateur->getId();
            }
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

    public static function remove_accents($string)
    {
        if (!preg_match('/[\x80-\xff]/', $string))
            return $string;

        $chars = array(
            // Decompositions for Latin-1 Supplement
            chr(195) . chr(128) => 'A', chr(195) . chr(129) => 'A',
            chr(195) . chr(130) => 'A', chr(195) . chr(131) => 'A',
            chr(195) . chr(132) => 'A', chr(195) . chr(133) => 'A',
            chr(195) . chr(135) => 'C', chr(195) . chr(136) => 'E',
            chr(195) . chr(137) => 'E', chr(195) . chr(138) => 'E',
            chr(195) . chr(139) => 'E', chr(195) . chr(140) => 'I',
            chr(195) . chr(141) => 'I', chr(195) . chr(142) => 'I',
            chr(195) . chr(143) => 'I', chr(195) . chr(145) => 'N',
            chr(195) . chr(146) => 'O', chr(195) . chr(147) => 'O',
            chr(195) . chr(148) => 'O', chr(195) . chr(149) => 'O',
            chr(195) . chr(150) => 'O', chr(195) . chr(153) => 'U',
            chr(195) . chr(154) => 'U', chr(195) . chr(155) => 'U',
            chr(195) . chr(156) => 'U', chr(195) . chr(157) => 'Y',
            chr(195) . chr(159) => 's', chr(195) . chr(160) => 'a',
            chr(195) . chr(161) => 'a', chr(195) . chr(162) => 'a',
            chr(195) . chr(163) => 'a', chr(195) . chr(164) => 'a',
            chr(195) . chr(165) => 'a', chr(195) . chr(167) => 'c',
            chr(195) . chr(168) => 'e', chr(195) . chr(169) => 'e',
            chr(195) . chr(170) => 'e', chr(195) . chr(171) => 'e',
            chr(195) . chr(172) => 'i', chr(195) . chr(173) => 'i',
            chr(195) . chr(174) => 'i', chr(195) . chr(175) => 'i',
            chr(195) . chr(177) => 'n', chr(195) . chr(178) => 'o',
            chr(195) . chr(179) => 'o', chr(195) . chr(180) => 'o',
            chr(195) . chr(181) => 'o', chr(195) . chr(182) => 'o',
            chr(195) . chr(182) => 'o', chr(195) . chr(185) => 'u',
            chr(195) . chr(186) => 'u', chr(195) . chr(187) => 'u',
            chr(195) . chr(188) => 'u', chr(195) . chr(189) => 'y',
            chr(195) . chr(191) => 'y',
            // Decompositions for Latin Extended-A
            chr(196) . chr(128) => 'A', chr(196) . chr(129) => 'a',
            chr(196) . chr(130) => 'A', chr(196) . chr(131) => 'a',
            chr(196) . chr(132) => 'A', chr(196) . chr(133) => 'a',
            chr(196) . chr(134) => 'C', chr(196) . chr(135) => 'c',
            chr(196) . chr(136) => 'C', chr(196) . chr(137) => 'c',
            chr(196) . chr(138) => 'C', chr(196) . chr(139) => 'c',
            chr(196) . chr(140) => 'C', chr(196) . chr(141) => 'c',
            chr(196) . chr(142) => 'D', chr(196) . chr(143) => 'd',
            chr(196) . chr(144) => 'D', chr(196) . chr(145) => 'd',
            chr(196) . chr(146) => 'E', chr(196) . chr(147) => 'e',
            chr(196) . chr(148) => 'E', chr(196) . chr(149) => 'e',
            chr(196) . chr(150) => 'E', chr(196) . chr(151) => 'e',
            chr(196) . chr(152) => 'E', chr(196) . chr(153) => 'e',
            chr(196) . chr(154) => 'E', chr(196) . chr(155) => 'e',
            chr(196) . chr(156) => 'G', chr(196) . chr(157) => 'g',
            chr(196) . chr(158) => 'G', chr(196) . chr(159) => 'g',
            chr(196) . chr(160) => 'G', chr(196) . chr(161) => 'g',
            chr(196) . chr(162) => 'G', chr(196) . chr(163) => 'g',
            chr(196) . chr(164) => 'H', chr(196) . chr(165) => 'h',
            chr(196) . chr(166) => 'H', chr(196) . chr(167) => 'h',
            chr(196) . chr(168) => 'I', chr(196) . chr(169) => 'i',
            chr(196) . chr(170) => 'I', chr(196) . chr(171) => 'i',
            chr(196) . chr(172) => 'I', chr(196) . chr(173) => 'i',
            chr(196) . chr(174) => 'I', chr(196) . chr(175) => 'i',
            chr(196) . chr(176) => 'I', chr(196) . chr(177) => 'i',
            chr(196) . chr(178) => 'IJ', chr(196) . chr(179) => 'ij',
            chr(196) . chr(180) => 'J', chr(196) . chr(181) => 'j',
            chr(196) . chr(182) => 'K', chr(196) . chr(183) => 'k',
            chr(196) . chr(184) => 'k', chr(196) . chr(185) => 'L',
            chr(196) . chr(186) => 'l', chr(196) . chr(187) => 'L',
            chr(196) . chr(188) => 'l', chr(196) . chr(189) => 'L',
            chr(196) . chr(190) => 'l', chr(196) . chr(191) => 'L',
            chr(197) . chr(128) => 'l', chr(197) . chr(129) => 'L',
            chr(197) . chr(130) => 'l', chr(197) . chr(131) => 'N',
            chr(197) . chr(132) => 'n', chr(197) . chr(133) => 'N',
            chr(197) . chr(134) => 'n', chr(197) . chr(135) => 'N',
            chr(197) . chr(136) => 'n', chr(197) . chr(137) => 'N',
            chr(197) . chr(138) => 'n', chr(197) . chr(139) => 'N',
            chr(197) . chr(140) => 'O', chr(197) . chr(141) => 'o',
            chr(197) . chr(142) => 'O', chr(197) . chr(143) => 'o',
            chr(197) . chr(144) => 'O', chr(197) . chr(145) => 'o',
            chr(197) . chr(146) => 'OE', chr(197) . chr(147) => 'oe',
            chr(197) . chr(148) => 'R', chr(197) . chr(149) => 'r',
            chr(197) . chr(150) => 'R', chr(197) . chr(151) => 'r',
            chr(197) . chr(152) => 'R', chr(197) . chr(153) => 'r',
            chr(197) . chr(154) => 'S', chr(197) . chr(155) => 's',
            chr(197) . chr(156) => 'S', chr(197) . chr(157) => 's',
            chr(197) . chr(158) => 'S', chr(197) . chr(159) => 's',
            chr(197) . chr(160) => 'S', chr(197) . chr(161) => 's',
            chr(197) . chr(162) => 'T', chr(197) . chr(163) => 't',
            chr(197) . chr(164) => 'T', chr(197) . chr(165) => 't',
            chr(197) . chr(166) => 'T', chr(197) . chr(167) => 't',
            chr(197) . chr(168) => 'U', chr(197) . chr(169) => 'u',
            chr(197) . chr(170) => 'U', chr(197) . chr(171) => 'u',
            chr(197) . chr(172) => 'U', chr(197) . chr(173) => 'u',
            chr(197) . chr(174) => 'U', chr(197) . chr(175) => 'u',
            chr(197) . chr(176) => 'U', chr(197) . chr(177) => 'u',
            chr(197) . chr(178) => 'U', chr(197) . chr(179) => 'u',
            chr(197) . chr(180) => 'W', chr(197) . chr(181) => 'w',
            chr(197) . chr(182) => 'Y', chr(197) . chr(183) => 'y',
            chr(197) . chr(184) => 'Y', chr(197) . chr(185) => 'Z',
            chr(197) . chr(186) => 'z', chr(197) . chr(187) => 'Z',
            chr(197) . chr(188) => 'z', chr(197) . chr(189) => 'Z',
            chr(197) . chr(190) => 'z', chr(197) . chr(191) => 's'
        );

        $string = strtr($string, $chars);

        return $string;
    }
}

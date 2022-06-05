<?php

namespace App\Entity;

use App\Repository\SouhaitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SouhaitRepository::class)]
class Souhait
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $nom;

    #[ORM\Column(type: 'boolean')]
    private $achete;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'souhaits')]
    #[ORM\JoinColumn(nullable: false)]
    private $utilisateur;

    public function getId(): ?int
    {
        return $this->id;
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

    public function isAchete(): ?bool
    {
        return $this->achete;
    }

    public function setAchete(bool $achete): self
    {
        $this->achete = $achete;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }
}

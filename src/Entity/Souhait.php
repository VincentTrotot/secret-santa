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
    private $destinataire;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $emetteur;

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

    public function getDestinataire(): ?Utilisateur
    {
        return $this->destinataire;
    }

    public function setDestinataire(?Utilisateur $destinataire): self
    {
        $this->destinataire = $destinataire;

        return $this;
    }

    public function getEmetteur(): ?Utilisateur
    {
        return $this->emetteur;
    }

    public function setEmetteur(?Utilisateur $emetteur): self
    {
        $this->emetteur = $emetteur;

        return $this;
    }

    public function __toString(): string
    {
        return $this->nom;
    }
}

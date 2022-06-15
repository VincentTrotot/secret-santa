<?php

namespace App\Entity;

use App\Repository\EchangeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EchangeRepository::class)]
class Echange
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'datetime')]
    private $date;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'demandesFaites')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private $demandeur;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'demandesRecues')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private $receveur;

    #[ORM\Column(type: 'string', length: 255)]
    private $status;

    public const STATUS_EN_ATTENTE = 'en_attente';
    public const STATUS_ACCEPTE = 'accepte';
    public const STATUS_REFUSE = 'refuse';
    public const STATUS_ANNULE = 'annule';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDemandeur(): ?Utilisateur
    {
        return $this->demandeur;
    }

    public function setDemandeur(?Utilisateur $demandeur): self
    {
        $this->demandeur = $demandeur;

        return $this;
    }

    public function getReceveur(): ?Utilisateur
    {
        return $this->receveur;
    }

    public function setReceveur(?Utilisateur $receveur): self
    {
        $this->receveur = $receveur;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\PronosticRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PronosticRepository::class)]
class Pronostic
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'datetime_immutable')]
    private $createdAt;

    #[ORM\Column(type: 'json')]
    private $dataUtilisateur = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDataUtilisateur(): ?array
    {
        return $this->dataUtilisateur;
    }

    public function setDataUtilisateur(array $dataUtilisateur): self
    {
        $this->dataUtilisateur = $dataUtilisateur;

        return $this;
    }

    public function getUtilisateur(): Utilisateur
    {
        /** @var Utilisateur $utilisateur */
        $utilisateur = unserialize($this->dataUtilisateur[0]);
        return $utilisateur;
    }
}

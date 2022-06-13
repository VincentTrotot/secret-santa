<?php

namespace App\Components;

use App\Entity\Echange;
use App\Repository\EchangeRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('echanges')]
class EchangesComponent
{
    public int $id;

    public function __construct(
        private EchangeRepository $echangeRepository,
    ) {
    }

    public function getEchanges(): array
    {
        return $this->echangeRepository->findForUtilisateur($this->id);
    }
}

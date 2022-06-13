<?php

namespace App\Repository;

use App\Entity\Souhait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Souhait>
 *
 * @method Souhait|null find($id, $lockMode = null, $lockVersion = null)
 * @method Souhait|null findOneBy(array $criteria, array $orderBy = null)
 * @method Souhait[]    findAll()
 * @method Souhait[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SouhaitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Souhait::class);
    }

    public function add(Souhait $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Souhait $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}

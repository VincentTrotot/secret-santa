<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Utilisateur>
 *
 * @method Utilisateur|null find($id, $lockMode = null, $lockVersion = null)
 * @method Utilisateur|null findOneBy(array $criteria, array $orderBy = null)
 * @method Utilisateur[]    findAll()
 * @method Utilisateur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UtilisateurRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    public function add(Utilisateur $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Utilisateur $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Utilisateur) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->add($user, true);
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('u')
            ->select('u, s')
            ->leftJoin('u.souhaits', 's')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_PARTICIPANT%')
            ->orderBy('u.dateDeNaissance', 'ASC')
            ->addOrderBy('s.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllByTire(?int $id): array
    {
        $results = $this->createQueryBuilder('u')
            ->select('u, s')
            ->leftJoin('u.souhaits', 's')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_PARTICIPANT%')
            ->orderBy('u.dateDeNaissance', 'ASC')
            ->addOrderBy('s.nom', 'ASC')
            ->getQuery()
            ->getResult();

        if ($id === null) {
            return $results;
        }

        usort($results, function ($a, $b) use ($id) {
            if ($a->getId() == $id) {
                return -1;
            } else if ($b->getId() == $id) {
                return 1;
            } else {
                return 0;
            }
        });

        return $results;
    }
}

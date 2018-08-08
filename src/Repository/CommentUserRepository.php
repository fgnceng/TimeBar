<?php

namespace App\Repository;

use App\Entity\CommentUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CommentUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommentUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommentUser[]    findAll()
 * @method CommentUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentUserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CommentUser::class);
    }

//    /**
//     * @return CommentUser[] Returns an array of CommentUser objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CommentUser
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

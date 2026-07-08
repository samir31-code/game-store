<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    //    /**
    //     * @return Produit[] Returns an array of Produit objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Produit
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function rechercher(string $query): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.categorie', 'c')
            ->leftJoin('p.etiquettes', 'e')
            ->where('p.nom LIKE :query')
            ->orWhere('p.description LIKE :query')
            ->orWhere('p.marque LIKE :query')
            ->orWhere('p.referenceProduit LIKE :query')
            ->orWhere('c.nom LIKE :query')
            ->orWhere('e.nom LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

<?php
namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Produit>
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

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

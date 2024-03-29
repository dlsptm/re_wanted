<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findByPrice($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.price < :val')
            ->setParameter('val', $value)
            ->orderBy('a.price', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findByPriceCategory($value, $category)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.price < :val', "a.category = $category")
            ->setParameter('val', $value)
            ->orderBy('a.price', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBySearch($value)
    {
        return $this->createQueryBuilder('p')
            ->where('p.title LIKE :val')
            ->setParameter('val', '%' . $value . '%')
            ->orderBy('p.title', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

//    /**
//     * @return Product[] Returns an array of Product objects
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

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

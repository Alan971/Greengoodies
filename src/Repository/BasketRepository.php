<?php

namespace App\Repository;

use App\Entity\Basket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<Basket>
 */
class BasketRepository extends ServiceEntityRepository
{
    private $em;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Basket::class);
        $this->em = $em;
    }

    
       /**
        * @return Basket[] Returns a Basket object
        */
       public function findByUser($id): array
       {
           return $this->createQueryBuilder('b')
               ->andWhere('b.InfoUser = :val')
               ->setParameter('val', $id)
               ->orderBy('b.id', 'ASC')
               ->setMaxResults(10)
               ->getQuery()
               ->getResult()
           ;
       }

       public function removeBasket($id): void
       {
           $baskets = $this->findByUser($id);
           dump ($baskets);
           foreach ($baskets as $basket) {
               if (!$basket->getBill()) {
                   foreach($basket->getBasketProducts() as $basketProduct) {
                       $this->em->remove($basketProduct);
                   }
                   $this->em->remove($basket);
               }
           } 
           $this->em->flush();
       }
    //    /**
    //     * @return Basket[] Returns an array of Basket objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Basket
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

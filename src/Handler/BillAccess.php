<?php

namespace App\Handler;

use App\Entity\Bill;
use App\Entity\User;
use App\Entity\Basket;
use Doctrine\ORM\EntityManagerInterface;

class BillAccess
{
    private $em;
    public function __construct( EntityManagerInterface $em)
    {
        $this->em = $em;
    }


    /**
     * get all bills of a user
     *
     * @param User $currentUser
     * @return array
     */
    public function getBills(User $currentUser): array
    {
        $infoUser = $currentUser->getInfoUser();
        $baskets = $this->em->getRepository(Basket::class)->findByUser($infoUser->getId());
        $bills =[];
        foreach ($baskets as $basket) {
            $bills[] = $this->em->getRepository(Bill::class)->findByBasket($basket);
        }
        return $bills;
    }

    /**
     * Create bill and update stocks
     * stock gestion should be separate in futures versions
     * 
     * @param Basket $basket
     * @return boolean
     */
    public function createBill(Basket $basket) :bool
    {
        $basketProducts = $basket->getBasketProducts();
        $prixTotal = 0;
        foreach ($basketProducts as $basketProduct) {
            $prixTotal += $basketProduct->getProduct()->getPrice() * $basketProduct->getQuantity();
        }
        if($prixTotal===0){
            return false;
        } else {
            //gestion de la facture
            $bill = new Bill();
            $bill->setBasket($basket);
            $bill->setPrice($prixTotal);
            $bill->setDate(new \DateTime());
            $bill->setUniqueNumber(Date('Y-m') . '-F00' . $basket->getId());
            $this->em->persist($bill);
            //gestion des stocks
            foreach ($basketProducts as $basketProduct) {
                $product = $basketProduct->getProduct();
                $product->setStock($product->getStock() - $basketProduct->getQuantity());
                $this->em->persist($product);
            }
            $this->em->flush();
            return true;
        }
    }

    /**
     * delete all bills of a user in case user wants to delete his account
     *
     * @param User $currentUser
     * @return void
     */
    public function deleteBills(User $currentUser): void
    {

        $bills = $this->getBills($currentUser);
        foreach ($bills as $bill) {
            $this->em->remove($bill);
        }
        $this->em->flush();
    }
}
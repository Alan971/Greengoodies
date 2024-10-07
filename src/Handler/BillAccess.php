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
        $bills = $this->em->getRepository(Bill::class)->findByBasket($baskets);

        return $bills;
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
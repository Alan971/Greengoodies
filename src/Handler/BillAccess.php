<?php

namespace App\Handler;

use App\Entity\Bill;
use App\Entity\User;
use App\Entity\Basket;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;

class BillAccess
{
    private $em;
    public function __construct( EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getBills(User $currentUser): array
    {
        $infoUser = $currentUser->getInfoUser();
        $baskets = $this->em->getRepository(Basket::class)->findByUser($infoUser->getId());
        $bills = $this->em->getRepository(Bill::class)->findByBasket($baskets);

        return $bills;
    }
}
<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use App\Entity\InfoUser;
use App\Entity\Product;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        //administrateur
        $user = new User();
        $user->setEmail('admin@greengoodies.fr');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setApiAccess(false);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'test'));
        $manager->persist($user);
        $manager->flush();
        $infoUser = new InfoUser();
        $infoUser->setFirstName('Admin');
        $infoUser->setLastName('Greengoodies');
        $infoUser->setEntryDate(new \DateTime());
        $infoUser->setUser($user);
        $manager->persist($infoUser);
        $manager->flush();

        //utilisateur
        $user = new User();
        $user->setEmail('user@greengoodies.fr');
        $user->setRoles(['ROLE_USER']);
        $user->setApiAccess(false);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'test'));
        $manager->persist($user);   
        $manager->flush();
        $infoUser = new InfoUser();
        $infoUser->setFirstName('User');
        $infoUser->setLastName('Greengoodies');
        $infoUser->setEntryDate(new \DateTime());
        $infoUser->setUser($user);
        $manager->persist($infoUser);
        $manager->flush();

        for ($i = 1; $i <= 9; $i++) {
            $product = new Product();
            $product->setName('Produit ' . $i);
            $product->setShortDescription('Description du produit ' . $i);
            $product->setLongDescription("Ceci s'apparente à l'ammorce d'une description longue du produit " . $i);
            $product->setPrice(100.2 * $i);
            $product->setStock(2*$i);
            $product->setEnable(true);
            $product->setPicture('img/products/product' . $i . '.png');
            $manager->persist($product);
        }
        $manager->flush();
    }
}

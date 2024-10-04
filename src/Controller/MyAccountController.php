<?php

namespace App\Controller;

use App\Entity\Basket;
use App\Entity\Bill;
use App\Entity\User;
use App\Entity\InfoUser;
use App\Handler\BillAccess;
use App\Repository\UserRepository;
use App\Repository\BillRepository;
use App\Repository\BasketRepository;
use App\Repository\InfoUserRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Ramsey\Uuid\Uuid;

class MyAccountController extends AbstractController
{
    #[Route('/myaccount', name: 'app_my_account')]
    public function index(BillAccess $billAccess ): Response
    {
        if ($this->getUser()) {
            $orders = [];
            $orders = $billAccess->getBills($this->getUser());

            return $this->render('my_account/index.html.twig', [
                'apiAccess' => $this->getUser()->isApiAccess(),
                'orders' => $orders,
            ]);
        }
        return $this->redirectToRoute('app_login');
    }


    #[Route('/myaccount/api', name: 'app_my_account_api')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function api(EntityManagerInterface $entityManager, BillAccess $billAccess, TagAwareCacheInterface $cachePool): Response
    {
        // modification de l'état de l'api utilisateur
        $user = $this->getUser();
        if ($user->isApiAccess()) {
            $user->setApiAccess(false);
        } else {
            $user->setApiAccess(true);
        }
        $entityManager->persist($user);
        $entityManager->flush();

        // récupération des commandes de l'utilisateur si elle n'est pas dans le cache
        $stringId = $this->getUser()->getId()->__toString();
        dump($stringId);
        $idCache = "user_" . $stringId;
        $orders = $cachePool->get($idCache, function(ItemInterface $item) use ($billAccess)
        {
            $item->tag('useraccount');
            $orders = [];
            $orders = $billAccess->getBills($this->getUser());
            $item->expiresAfter(3600); //le cache dure 1 heure. il expire au bout du même tempe que le JWT
            return $orders;
        }
        );

        return $this->render('my_account/index.html.twig', [
            'apiAccess' => $this->getUser()->isApiAccess(),
            'orders' => $orders,
        ]);
    }

    #[Route('/myaccount/delete', name: 'app_my_account_delete')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(UserRepository $userRepository, Request $request): Response
    {
        //suppresion de l'utilisateur en bdd
        $user = $this->getUser();
        $userRepository->deleteUser($user);
        
        //suppression de la session, obligatoire avant de rediriger vers une page 
        $request->getSession()->invalidate(false);
        $this->container->get('security.token_storage')->setToken(null);

        $this->addFlash('danger', 'Votre compte a été supprimé');
        return $this->redirectToRoute('app_home');
    }
}

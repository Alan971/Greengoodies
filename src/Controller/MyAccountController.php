<?php

namespace App\Controller;

use App\Handler\BillAccess;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class MyAccountController extends AbstractController
{
    #[Route('/myaccount', name: 'app_my_account')]
    public function index(BillAccess $billAccess,TagAwareCacheInterface $cachePool ): Response
    {
        if ($this->getUser()) {
            // récupération des commandes de l'utilisateur si elle n'est pas dans le cache
            $stringId = $this->getUser()->getId()->__toString();
            $idCache = "user_" . $stringId;
            $orders = $cachePool->get($idCache, function(ItemInterface $item) use ($billAccess)
            {
                $item->tag('useraccount');
                $orders = [];
                $orders = $billAccess->getBills($this->getUser());
                $item->expiresAfter(1800); //le cache dure 1/2 heure.
                return $orders;
            }
            );

            return $this->render('my_account/index.html.twig', [
                'apiAccess' => $this->getUser()->isApiAccess(),
                'orders' => $orders,
            ]);
        }
        return $this->redirectToRoute('app_login');
    }


    #[Route('/myaccount/api', name: 'app_my_account_api')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function api(EntityManagerInterface $entityManager, BillAccess $billAccess, 
                        TagAwareCacheInterface $cachePool): Response
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
        $idCache = "user_" . $stringId;
        $orders = $cachePool->get($idCache, function(ItemInterface $item) use ($billAccess)
        {
            $item->tag('useraccount');
            $orders = [];
            $orders = $billAccess->getBills($this->getUser());
            $item->expiresAfter(1800); //le cache dure 1/2 heure.
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

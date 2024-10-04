<?php

namespace App\Controller;

use App\Entity\Basket;
use App\Entity\User;
use App\Entity\InfoUser;
use App\Repository\UserRepository;
use App\Repository\BillRepository;
use App\Repository\BasketRepository;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Polyfill\Intl\Idn\Info;

class MyAccountController extends AbstractController
{
    #[Route('/myaccount', name: 'app_my_account')]
    public function index(BillRepository $billRepository, BasketRepository $basketRepository, UserRepository $userRepository, InfoUserRepository $infoUserRepository): Response
    {
        if ($this->getUser()) {

            $basket = $this->getUser()->findBasket();
            $orders = [];
            $orders = $billRepository->findByUser($this->getUser());

            return $this->render('my_account/index.html.twig', [
                'apiAccess' => $this->getUser()->isApiAccess(),
                'orders' => $orders,
            ]);
        }
        return $this->redirectToRoute('app_login');
    }


    #[Route('/myaccount/api', name: 'app_my_account_api')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function api(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if ($user->isApiAccess()) {
            $user->setApiAccess(false);
        } else {
            $user->setApiAccess(true);
        }
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->render('my_account/index.html.twig', [
            'apiAccess' => $this->getUser()->isApiAccess(),
        ]);
    }

    #[Route('/myaccount/delete', name: 'app_my_account_delete')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(UserRepository $userRepository, Session $session, Request $request): Response
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

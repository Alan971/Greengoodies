<?php

namespace App\Controller;

use App\Handler\BillAccess;
use App\Repository\BasketRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class MyAccountController extends AbstractController
{
    /**
     * Affichage de la page de compte
     *
     * @param BillAccess $billAccess
     * @return Response
     */
    #[Route('/myaccount', name: 'app_my_account')]
    public function index(BillAccess $billAccess): Response
    {
        if ($this->getUser()) {

            $orders = $billAccess->getBills($this->getUser());

            return $this->render('my_account/index.html.twig', [
                'apiAccess' => $this->getUser()->isApiAccess(),
                'orders' => $orders,
            ]);
        }
        return $this->redirectToRoute('app_login');
    }

    /**
     * Activation ou désactivation de l'accès API
     *
     * @param EntityManagerInterface $entityManager
     * @param BillAccess $billAccess
     * @return Response
     */
    #[Route('/myaccount/api', name: 'app_my_account_api')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function api(EntityManagerInterface $entityManager, BillAccess $billAccess ): Response
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

        $orders = [];
        $orders = $billAccess->getBills($this->getUser());

        return $this->render('my_account/index.html.twig', [
            'apiAccess' => $this->getUser()->isApiAccess(),
            'orders' => $orders,
        ]);
    }

    /**
     * suppression de compte
     *
     * @param UserRepository $userRepository
     * @param Request $request
     * @param BillAccess $billAccess
     * @return Response
     */
    #[Route('/myaccount/delete', name: 'app_my_account_delete')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(UserRepository $userRepository, BasketRepository $basketRepository, Request $request, BillAccess $billAccess): Response
    {
        //suppresion de l'utilisateur en bdd
        $user = $this->getUser();
        $infoUser = $user->getInfoUser();
        $basketRepository->removeAllBaskets($infoUser->getId());
        $userRepository->deleteUser($user);
        // suppression des commandes de l'utilisateur
        // TO DO controler le bon fonctionnement de la suppression des commandes
 
        //TO DO suppression des baskets de l'utilisateur
        
        //suppression de la session, obligatoire avant de rediriger vers une page 
        $request->getSession()->invalidate(false);
        $this->container->get('security.token_storage')->setToken(null);

        $this->addFlash('danger', 'Votre compte a été supprimé');
        return $this->redirectToRoute('app_home');
    }
}

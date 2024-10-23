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

#[Route('/myaccount', name: 'app_my_')]
class MyAccountController extends AbstractController
{

    public function __construct(private BillAccess $billAccess)
    {
        
    }
    /**
     * Show page account
     *
     * @param BillAccess $billAccess
     * @return Response
     */
    #[Route('/', name: 'account')]
    public function index(): Response
    {
        if ($this->getUser()) {

            $orders = $this->billAccess->getBills($this->getUser());

            return $this->render('my_account/index.html.twig', [
                'apiAccess' => $this->getUser()->isApiAccess(),
                'orders' => $orders,
            ]);
        }
        return $this->redirectToRoute('app_login');
    }

    /**
     * Activation or désactivation of API Access
     *
     * @param EntityManagerInterface $entityManager
     * @param BillAccess $billAccess
     * @return Response
     */
    #[Route('/api', name: 'account_api')]
    public function api(EntityManagerInterface $entityManager ): Response
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
        $orders = $this->billAccess->getBills($this->getUser());

        return $this->render('my_account/index.html.twig', [
            'apiAccess' => $this->getUser()->isApiAccess(),
            'orders' => $orders,
        ]);
    }

    /**
     * Delete account
     *
     * @param UserRepository $userRepository
     * @param Request $request
     * @param BillAccess $billAccess
     * @return Response
     */
    #[Route('/delete', name: 'account_delete')]
    public function delete(UserRepository $userRepository, BasketRepository $basketRepository, Request $request): Response
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

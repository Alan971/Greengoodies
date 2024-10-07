<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Handler\ProductAccess;
use App\Repository\BasketRepository;
use App\Repository\ProductRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class BasketController extends AbstractController
{
    /**
     *  ajoute un produit au panier
     *
     * @param ProductAccess $productAccess
     * @param string $productId
     * @param ProductRepository $productRepository
     * @return Response
     */
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/basket/add/{productId}', name: 'app_add_to_basket')]
    public function addTobasket(ProductAccess $productAccess, string $productId, ProductRepository $productRepository): Response
    {
        if ($productAccess->AddProductToCart($this->getUser(), $productId)){
            $this->addFlash(
                'danger',
                "Le produit a bien été ajouté au panier"
            );
        } else {
            $this->addFlash(
                'danger',
                "Le produit n'existe pas ou n'est plus en stock"
            );
        }
        $products = $productRepository->findByEnable(true);
        return $this->render('home/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/basket/delete', name: 'app_basket_delete')]
    public function deleteBasket(BasketRepository $basketRepository ): Response
    {
        $user = $this->getUser();
        $userInfo = $user->getInfoUser();
        
        $basketRepository->removeBasket($userInfo);
        return $this->render('basket/index.html.twig', [
            'basketProducts' => '',
            'prixTotal' => 0
        ]);
    }

    /**
     *  affiche le panier de l'utilisateur
     * 
     * @param BasketRepository $basketRepository
     * @return Response
     */
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/basket', name: 'app_basket')]
    public function Index(BasketRepository $basketRepository): Response
    {
        $infoUser = $this->getUser()->getInfoUser();
        $baskets = $basketRepository->findByUser($infoUser->getId());
        foreach ($baskets as $cart) {
            if(!$cart->getBill()){
                $basket = $cart;
            }
        }
        if(!isset($basket)){
            return $this->render('basket/index.html.twig', [
                'basketProducts' => '',
                'prixTotal' => 0
            ]);
        } 
        $basketProducts = $basket->getBasketProducts();
        $prixTotal = 0;
        foreach ($basketProducts as $basketProduct) {
            $prixTotal += $basketProduct->getProduct()->getPrice() * $basketProduct->getQuantity();
        }

        return $this->render('basket/index.html.twig', [
            'basketProducts' => $basket->getBasketProducts(),
            'prixTotal' => $prixTotal
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/basket/bill', name: 'app_basket_valid')]
    public function validBill(BasketRepository $basketRepository): Response
    {
        
    }

}

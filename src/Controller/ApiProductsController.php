<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

class ApiProductsController extends AbstractController
{
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/api', name: 'app_api_products')]
    public function showProducts(ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        $products = $productRepository->findByEnable(true);
        //mise en forme du chemin complet des images
        foreach ($products as $product) {
            if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                $url = "https://"; 
            }
            else {
                $url = "http://"; 
            }
            $product->setPicture($url . $_SERVER['HTTP_HOST'] . $product->getPicture());
        }
        $jsonproduts = $serializer->serialize($products, 'json', ['groups' => ['products']]);

        return new JsonResponse( $jsonproduts, 200,[], true);          
    }

}

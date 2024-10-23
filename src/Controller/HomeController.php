<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProductRepository;

class HomeController extends AbstractController
{
    /**
     * Access to home page
     *
     * @param ProductRepository $productRepository
     * @return Response
     */
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findByEnable(true);
        return $this->render('home/index.html.twig', [
            'products' => $products,
        ]);
    }

    /**
     * Access to one product
     *
     * @param int $id
     * @param ProductRepository $productRepository
     * @return Response
     */
    #[Route('/product/{id}', name: 'app_product_show')]
    public function show(?int $id, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('No product found for id ' . $id);
        }
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
}

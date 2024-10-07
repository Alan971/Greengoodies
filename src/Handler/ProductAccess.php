<?php 

namespace App\Handler;

use App\Entity\Product;
use App\Entity\User;
use App\Entity\Basket;
use App\Entity\BasketProduct;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProductAccess
{
    private $productRepository;
    private $em;

    public function __construct(ProductRepository $productRepository, EntityManagerInterface $em)
    {
        $this->productRepository = $productRepository;
        $this->em = $em;
    }

    /**
     * get all products of a user
     *
     * @param User $currentUser
     * @param Int $productId
     * @return bool
     */
    public function AddProductToCart(User $user, string $productId): bool
    {
            $productId = intval($productId);
            $basket = new Basket();
            //s'il n'a pas encore de panier
            if (!$user->getInfoUser()->getBaskets()[0]) {
                //on crée un nouveau panier
                dump('ici');
                $basket->setInfoUser($user->getInfoUser());
                dump('là');
            } else {
                //s'il a déjà un panier on cherche celui qui n'a pas de bill
                $baskets = $user->getInfoUser()->getBaskets();
                foreach ($baskets as $cart) {
                    if (!$cart->getBill()) {
                        $basket = $cart;
                    }
                    else {
                        // s'il n'y en a pas, on crée un nouveau panier
                        $basket->setInfoUser($user->getInfoUser());                       
                    }
                }
            }
            $basket->setDate(new \DateTime());
            $basket->setBill(null);
            $this->em->persist($basket);
            //on ajoute le produit au panier
            $product = $this->em->getRepository(Product::class)->find($productId);
            // si le produit existe
            if ($product){
                // s'il est en stock
                if ($product->getStock() > 0) {
                    // si le produit est déjà dans le panier alors j'augmente la quantité
                    $basketProduct = $this->em->getRepository(BasketProduct::class)->findOneBy(['basket' => $basket, 'product' => $product]);
                    if ($basketProduct) {
                        $basketProduct->setQuantity($basketProduct->getQuantity() + 1);
                    } else {
                        $basketProduct = new BasketProduct();
                        $basketProduct->setProduct($product);
                        $basketProduct->setBasket($basket);
                        $basketProduct->setQuantity(1);
                    }
                    $this->em->persist($basketProduct);
                    $this->em->flush();
                }
                else {
                    return false;
                }
            } else {
                return false;
            }
        return true;
    }
}
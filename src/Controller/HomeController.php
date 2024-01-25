<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(ProductRepository $repository, CartService $cartService): Response
    {
            $products = $repository->findAll();
            $cart = $cartService->getCartWithData();


        return $this->render('home/home.html.twig', [
            'products' => $products,
            'cart'=>$cart
        ]);
    }


    #[Route('/product/{id}', name: 'home_product')]
     public function product (ProductRepository $repository, $id=null):Response
    {
            $product = $repository->find($id);
        
        return $this->render('home/product.html.twig', [
            'product' => $product
        ]);
    }
    

    #[Route('/cart/add/{id}/{target}', name: 'add_cart')]
     public function cart_add (CartService $cartService, $id, $target):Response
    {   
        $cartService->add($id);
        $this->addFlash('success', 'Produit ajouté au panier');
        return $this->redirectToRoute($target);
    }
    

    #[Route('/cart/remove/{id}/{target}', name: 'remove_cart')]
     public function cart_remove (CartService $cartService, $id, $target):Response
    {

        $cartService->remove($id);
        return $this->redirectToRoute($target);
    }


    #[Route('/cart/delete/{id}', name: 'delete_cart')]
     public function cart_delete (CartService $cartService, $id):Response
    {
        $cartService->delete($id);
        return $this->redirectToRoute('cart');
    }


    #[Route('/cart/destroy', name: 'destroy_cart')]
     public function cart_destroy (CartService $cartService):Response
    {
        $cartService->destroy();
        return $this->redirectToRoute('home');
    }

    #[Route('/cart', name: 'cart')]
     public function cart (CartService $cartService,):Response
    {
        $cart = $cartService->getCartWithData();
        $total = $cartService->getTotal();

    return $this->render('home/cart.html.twig', [
         'cart' => $cart,
         'total' => $total
    ]);
    }
}
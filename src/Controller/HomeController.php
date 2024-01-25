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
    public function home(ProductRepository $repository): Response
    {
            $products = $repository->findAll();

        return $this->render('home/home.html.twig', [
            'products' => $products
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
    

    #[Route('/cart/add/{id}', name: 'add_cart')]
     public function cart_add (CartService $cartService, $id):Response
    {   
        $cartService->add($id);
        return $this->redirectToRoute('home');
    }
    

    #[Route('/cart/remove/{id}', name: 'remove_cart')]
     public function cart_remove (CartService $cartService, $id):Response
    {

        $cartService->remove($id);
        return $this->redirectToRoute('home');
    }


    #[Route('/cart/delete/{id}', name: 'delete_cart')]
     public function cart_delete (CartService $cartService, $id):Response
    {
        $cartService->delete($id);
        return $this->redirectToRoute('home');
    }


    #[Route('/cart/destroy', name: 'destroy_cart')]
     public function cart_destroy (CartService $cartService):Response
    {
        $cartService->destroy();
        return $this->redirectToRoute('home');
    }

    #[Route('/cart', name: 'cart')]
     public function cart (CartService $cartService):Response
    {
    return $this->render('home/home.html.twig');
    }
}
<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(ProductRepository $repository, $id=null): Response
    {
            $products = $repository->findAll();

        return $this->render('home/home.html.twig', [
            'controller_name' => 'HomeController',
            'products' => $products
        ]);
    }

    #[Route('/{id}', name: 'home_product')]
     public function product (ProductRepository $repository, $id=null):Response
    {
            $product = $repository->find($id);
        
        return $this->render('home/home_product.html.twig', [
            'controller_name' => 'HomeProductController',
            'product' => $product
        ]);
    }
}
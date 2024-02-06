<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
      'cart' => $cart
    ]);
  }


  #[Route('/product/{id}', name: 'home_product')]
  public function product(ProductRepository $repository, $id = null): Response
  {
    $product = $repository->find($id);

    return $this->render('home/product.html.twig', [
      'product' => $product
    ]);
  }


  #[Route('/cart/add/{id}/{target}', name: 'add_cart')]
  public function cart_add(CartService $cartService, $id, $target): Response
  {
    $cartService->add($id);
    $this->addFlash('success', 'Produit ajouté au panier');
    return $this->redirectToRoute($target);
  }


  #[Route('/cart/remove/{id}/{target}', name: 'remove_cart')]
  public function cart_remove(CartService $cartService, $id, $target): Response
  {

    $cartService->remove($id);
    return $this->redirectToRoute($target);
  }


  #[Route('/cart/delete/{id}', name: 'delete_cart')]
  public function cart_delete(CartService $cartService, $id): Response
  {
    $cartService->delete($id);
    return $this->redirectToRoute('cart');
  }


  #[Route('/cart/destroy', name: 'destroy_cart')]
  public function cart_destroy(CartService $cartService): Response
  {
    $cartService->destroy();
    return $this->redirectToRoute('home');
  }

  #[Route('/cart', name: 'cart')]
  public function cart(CartService $cartService,): Response
  {
    $cart = $cartService->getCartWithData();
    $total = $cartService->getTotal();

    return $this->render('home/cart.html.twig', [
      'cart' => $cart,
      'total' => $total
    ]);
  }

  #[Route('/profile/{id}', name: 'profile')]
   public function profile (UserRepository $repository, $id):Response
  {
    $user = $repository->find($id);

  return $this->render('home/profile.html.twig', [
    'user'=>$user
  ]);
  }

  #[Route('/comments/{id}', name: 'comments')]
    public function comments(Product $product, Request $request, EntityManagerInterface $manager)
    {


        $comments = $product->getRatings();


        return $this->render('home/comments.html.twig', [
            'comments' => $comments
        ]);
    }

    #[Route('/search', name: 'app_search', methods: 'GET')]
    public function searchAction(Request $request, ProductRepository $productRepository,CategoryRepository $categoryRepository,TagRepository $tagRepository)
    {


        $requestString = $request->get('q');

        $productsFromProduct = $productRepository->findBySearch($requestString);
        $productsFromTag = $tagRepository->findBySearch($requestString);
        $productsFromCategory = $categoryRepository->findBySearch($requestString);
        $result['search']=$requestString;
        if (!$productsFromProduct && !$productsFromCategory && !$productsFromTag) {
            $result['entities']['error'] = "Aucun résultat";
        } else {
           if ($productsFromProduct){
               $result['entities']['Produits'] = $this->getRealEntities($productsFromProduct);
               $result['entities']['Produits']['count']=count($productsFromProduct);

           }
           if ($productsFromTag )
           {
               $result['entities']['Tags'] = $this->getRealEntities($productsFromTag);
               $result['entities']['Tags']['count']=0;
               foreach ($productsFromTag as $tag){

                   $products=$tag->getProducts();
                   $result['entities']['Tags']['count']+=count($products);

               }

              // $result['entities']['Tags']['count']=10;

           }

           if ($productsFromCategory)
           {
               $result['entities']['Catégorie'] = $this->getRealEntities($productsFromCategory);
               $products=$productRepository->findBy(['category'=>$productsFromCategory]);
               $result['entities']['Catégorie']['count']=count($products);

           }



        }

        return new Response(json_encode($result));
    }

    public function getRealEntities($entities): array
    {
        $realEntities=[];
        foreach ($entities as $entity) {
            $realEntities[$entity->getId()] = $entity->getTitle();
        }

        return $realEntities;
    }


    #[Route('/search/{entity}/{id}', name: 'final_search')]
    public function finalSearch(Request $request, ProductRepository $productRepository,CategoryRepository $categoryRepository,TagRepository $tagRepository, $entity, $id)
    {
        if ($entity=='Produits'){


          if (!is_numeric($id)){

              $products=$productRepository->findBySearch($id);
          }else{
              $products=$productRepository->findBy(['id'=>$id]);

          }

           $count= count($products);
        }

        if ($entity=='Catégorie'){
            $category=$categoryRepository->find($id);
            $products=$productRepository->findBy(['category'=>$category]);
            $count= count($products);
        }

        if ($entity=='Tags'){
            $tag=$tagRepository->find($id);
            $products=$tag->getProducts();
            $count= count($products);
        }


        return $this->render('home/display_products.html.twig', [
            'products'=>$products,
            'count'=>$count,
            'type'=>$entity
        ]);

    }
}

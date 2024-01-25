<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Product;
use App\Form\MediaType;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/product')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'admin_product')]
    #[Route('/{id}', name: 'admin_product_update')]
    public function index(ProductRepository $repository, Request $request, EntityManagerInterface $manager, $id = null): Response
    {   
        // récupérer la liste des catégories (SELECT)
        // injection de dépendance via parametre

        if ($id) {
            $product = $repository->find($id);
        } else {
            $product = New Product();
        }

        $products = $repository->findAll();


         // Request est une classe prédéfinie pour faire des insert, update et delete. Pour select on utilise la classe prédéfinie Entity


            // génération de formulaire à partir de la classe ProductFormType
            $form = $this->createForm(ProductType::class, $product);

            
            // ici on va gérer la requête entrante
            $form->handleRequest($request);

            // on va vérifier si le formulaire a été soumis et est valide

            if ($form->isSubmitted() && $form->isValid())
            {
                // on crée une instance de la classe Product à laquelle on passe ces valeurs
                // on récupère les valeurs du formulaire 
                $product = $form->getData();

                // On persiste les valeurs
                $manager->persist($product);

                // On exécute la transaction
                $manager->flush();

                if ($id) {
                    $this->addFlash('success', 'Votre produit a bien été modifié');
                } else {
                    $this->addFlash('success', 'Votre produit a bien été ajouté');

                    return $this->redirectToRoute('media_create', ['id' => $product->getId()] );
                }
            }


           return $this->render('product/index.html.twig', [
            'products' => $products,
            'form' => $form->createView(),
           ]);
           }

           #[Route('/delete/{id}', name: 'admin_product_delete')]
            public function delete (ProductRepository $repository, EntityManagerInterface $manager,  $id=null):Response
           {
               if ($id) {
                $product = $repository->find($id);
                $manager->remove($product);
                $manager->flush();

                return $this->redirectToRoute('admin_product');
               }
           }

 // méthode sur laquelle on est redirigée après la création d'un produit
    // l'id est l'id du product pour mettre en liens le média avec le produit
    #[Route('/media/create/{id}', name: 'media_create')]
    public function media_create(Request $request, EntityManagerInterface $manager, ProductRepository $repository, $id): Response
    {
        $media = new Media();

        $form = $this->createForm(MediaType::class, $media);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $product = $repository->find($id);

            $file = $form->get('src')->getData();

            $nbMedia = count($product->getMedias()) + 1;
            
            $filename= date('d-m-Y-H-i-s').'-'.$product->getTitle().$nbMedia.'.'.$file->getClientOriginalExtension();

            $file->move($this->getParameter('upload_dir'), $filename);
            

            $media->setSrc($filename);
            $media->setTitle($product->getTitle().($nbMedia));

            // On persiste les valeurs 
            $manager->persist($media);


            $product->addMedia($media);
            $manager->persist($product);

            $manager->flush();
        }
    
        return $this->render('product/media_create.html.twig',[
            'form' => $form->createView(),
            'medias' => $media
        ]);
    
    } 


    #[Route('/detail/{id}', name: 'product_detail')]
     public function product_detail (ProductRepository $repository, $id):Response
    {
        $product=$repository->find($id);

    return $this->render('product/product_detail.html.twig',[
        'product' => $product
    ]);
    }
}
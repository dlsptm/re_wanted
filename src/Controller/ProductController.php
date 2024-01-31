<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Product;
use App\Form\MediaType;
use App\Form\ProductType;
use App\Repository\MediaRepository;
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
    #[Route('/update/{id}', name: 'admin_product_update')]
    public function index(ProductRepository $repository, Request $request, EntityManagerInterface $manager, $id = null): Response
    {
        // AFFICHAGE DES PRODUITS
        $products = $repository->findAll();
        // récupérer la liste des produits (SELECT * FROM product)
        
        // MODIFICATION DU PRODUCT
        if($id){
            $product = $repository->find($id);
            
        } else {
            $product = new Product();
        }
         // AJOUT DU PRODUCT

         // génération du formulaire à partir de la classe ProductType
         $form = $this->createForm(ProductType::class, $product);

         // ici on va gérer la requête entrante
         $form->handleRequest($request);
 
         // on va vérifier si le formulaire a été soumis et est valide
         if ($form->isSubmitted() && $form->isValid()){
 
             // on récupère les valeurs du formulaire
             
             $product = $form->getData();
 
             // on persiste les valeurs
 
             $manager->persist($product);
 
             // on exécute la transaction
             $manager->flush();

             if ($id){
                $this->addFlash('success', 'Le produit a bien été modifié');
                return $this->redirectToRoute('product_list');
                }else{
                $this->addFlash('success', 'Le produit a bien été ajouté');
                return $this->redirectToRoute('media_create', ['id' => $product->getId()]);
                }
            }
           
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
            'products' => $products,
            'form' => $form->createView()
        ]);
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
            // on recupère le fichier uploader
            $file = $form->get('src')->getData();
            // nombre de médias existant en lien avec le produit que l'on incrémente de 1 pour concaténé au title du média ainsi qu'au renommage du src ci-dessous
            $number = count($product->getMedias()) + 1;

            // on renomme le fichier en le concaténant avec date complète, son numéro puis nom d'origine du fichier (le title qui est celui du produit) et enfin son extension
            $file_name = date('Y-m-d-H-i-s') . '-' . $product->getTitle() . $number . '.' . $file->getClientOriginalExtension();

            // on upload en ayant préalablement configuré le parameter 'upload_dir' dans le services.yaml de Config
            //upload_dir: '%kernel.project_dir%/public/upload'
            $file->move($this->getParameter('upload_dir'), $file_name);

            $media->setSrc($file_name);
            $media->setTitle($product->getTitle().$number);
            $manager->persist($media);
            $product->addMedia($media);
            $manager->persist($product);
            $manager->flush();

            $this->addFlash('success', 'Média créé, vous pouvez en ajouter un autre et valider ou cliquer sur terminé pour voir le détail');

            return $this->redirectToRoute('media_create', ['id' => $product->getId()]);

        }
    
        return $this->render('product/media_create.html.twig',[
            'form' => $form->createView()
        ]);
    
    }

    #[Route('/update/media/{id}', name: 'media_update')]
     public function updateMedia (Product $product):Response
    {

        return $this->render('product/update_media.html.twig', compact('product'));
    }

    #[Route('/delete/media/', name: 'media_delete')]
     public function deleteMedia (Request $request, EntityManagerInterface $manager, MediaRepository $repository):Response
    {
        $medias_id = $request->request->all()['medias'];
        foreach ($medias_id as $media_id)
        {
            $media=$repository->find($media_id);
            $id = $media->getProduct()->getId();
            
            unlink($this->getParameter('upload_dir').'/'.$media->getSrc());

            $manager->remove($media);

        }
        $manager->flush();
        return $this->redirectToRoute('product_detail', compact('id'));
    }
    

     // SUPPRESSION DES PRODUITS
    #[Route('/delete/{id}', name: 'admin_product_delete')]
    public function delete(ProductRepository $repository, EntityManagerInterface $manager, $id = null)
    {   if($id)
        {
            $product = $repository->find($id);
            $manager->remove($product);
            $manager->flush();
            
            return $this->redirectToRoute('admin_product');
        }
    
    }

    // méthode pour la page de gestion des produits renvoyant products.html.twig
    #[Route('/list', name: 'product_list')]
    public function products_list(ProductRepository $repository)
    {
        $products = $repository->findAll();

        return $this->render('product/products.html.twig', [
            'products' => $products
        ]);
    }

     // AFFICHAGE DES DETAILS DES PRODUITS
    #[Route('/detail/{id}', name: 'product_detail')]
    public function products_detail(ProductRepository $repository, $id)
    {
        $product = $repository->find($id);

        return $this->render('product/product_detail.html.twig', [
            'product' => $product
        ]);
    }
}

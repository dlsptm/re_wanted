<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryFormType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/category')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'admin_category')]
    #[Route('/{id}', name: 'admin_category_update')]
    public function index(CategoryRepository $repository, Request $request, EntityManagerInterface $manager, $id = null): Response
    {   
        // récupérer la liste des catégories (SELECT)
        // injection de dépendance via parametre

        if ($id) {
            $category = $repository->find($id);
        } else {
            $category = New Category();
        }

        $categories = $repository->findAll();


         // Request est une classe prédéfinie pour faire des insert, update et delete. Pour select on utilise la classe prédéfinie Entity


            // génération de formulaire à partir de la classe CategoryFormType
            $form = $this->createForm(CategoryFormType::class, $category);

            
            // ici on va gérer la requête entrante
            $form->handleRequest($request);

            // on va vérifier si le formulaire a été soumis et est valide

            if ($form->isSubmitted() && $form->isValid())
            {
                // on crée une instance de la classe Category à laquelle on passe ces valeurs
                // on récupère les valeurs du formulaire 
                $category = $form->getData();

                // On persiste les valeurs
                $manager->persist($category);

                // On exécute la transaction
                $manager->flush();

                return $this->redirectToRoute('admin_category');
            }


           return $this->render('category/index.html.twig', [
            'categories' => $categories,
            'form' => $form->createView(),
           ]);
           }

           #[Route('/delete/{id}', name: 'admin_category_delete')]
            public function delete (CategoryRepository $repository, EntityManagerInterface $manager,  $id=null):Response
           {
               if ($id) {
                $category = $repository->find($id);
                $manager->remove($category);
                $manager->flush();

                return $this->redirectToRoute('admin_category');
               }
           }

}

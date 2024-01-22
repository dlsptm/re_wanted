<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Form\TagType;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/tag')]
class TagController extends AbstractController
{
    #[Route('/', name: 'admin_tag')]
    #[Route('/{id}', name: 'admin_tag_update')]
    public function index(TagRepository $repository, Request $request, EntityManagerInterface $manager, $id = null): Response
    {   
        // récupérer la liste des catégories (SELECT)
        // injection de dépendance via parametre

        if ($id) {
            $Tag = $repository->find($id);
        } else {
            $Tag = New Tag();
        }

        $tags = $repository->findAll();

         // Request est une classe prédéfinie pour faire des insert, update et delete. Pour select on utilise la classe prédéfinie Entity

            // génération de formulaire à partir de la classe TagFormType
            $form = $this->createForm(TagType::class, $Tag);

            
            // ici on va gérer la requête entrante
            $form->handleRequest($request);

            // on va vérifier si le formulaire a été soumis et est valide

            if ($form->isSubmitted() && $form->isValid())
            {
                // on crée une instance de la classe Tag à laquelle on passe ces valeurs
                // on récupère les valeurs du formulaire 
                $Tag = $form->getData();

                // On persiste les valeurs
                $manager->persist($Tag);

                // On exécute la transaction
                $manager->flush();

                return $this->redirectToRoute('admin_tag');
            }


           return $this->render('tag/index.html.twig', [
            'tags' => $tags,
            'form' => $form->createView(),
           ]);
           }

           #[Route('/delete/{id}', name: 'admin_tag_delete')]
            public function delete (TagRepository $repository, EntityManagerInterface $manager,  $id=null):Response
           {
               if ($id) {
                $Tag = $repository->find($id);
                $manager->remove($Tag);
                $manager->flush();

                return $this->redirectToRoute('admin_tag');
               }
           }

}
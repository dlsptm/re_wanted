<?php

namespace App\Controller;


use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/admin', name: 'dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig', [

        ]);
    }

    #[Route('admin/users/', name: 'users_management')]
    #[Route('admin/users/active/{id}/{active}', name: 'users_management_active')]
    public function userManagement (UserRepository $repository, EntityManagerInterface $manager, $id = null, $active= null):Response
    {
        if ($id) {
            $users = $repository->find($id);
            $users->setActive($active);
            $manager->persist($users);
            $manager->flush();

            if ($users->getActive() == 1 ) {
                $this->addFlash('success', 'Vous avez activé l\'utilisateur');

            } else {
                $this->addFlash('success', 'Vous avez désactivé l\'utilisateur'); 
            }

            return $this->redirectToRoute('users_management');
        } else {
            $users = $repository->findAll();
        }

      return $this->render('admin/user_management.html.twig', compact('users'));
    }

    #[Route('/admin/users/role/{id}/{role}', name: 'users_management_role')]
     public function adminUpdate (UserRepository $repository, EntityManagerInterface $manager, $id = null, $role=null):Response
    {
        $user = $repository->find($id);
        $user->setRoles($role);
        $manager->persist($user);
        $manager->flush();

        $this->addFlash('success', 'Vous avez bien modifié le rôle de l\'utilisateur');

    return $this->redirectToRoute('users_management');
    
    }

    
}
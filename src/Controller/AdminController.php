<?php

namespace App\Controller;

use App\Repository\OrderPurchaseRepository;
use App\Repository\RatingRepository;
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
        $user->setRoles([$role]);
        $manager->persist($user);
        $manager->flush();

        $this->addFlash('success', 'Vous avez bien modifié le rôle de l\'utilisateur');

    return $this->redirectToRoute('users_management');
    
    }


    #[Route('/orders', name: 'orders')]
    #[Route('/order/status/{id}/{status}', name: 'order_status_upgrade')]
    public function orders(OrderPurchaseRepository $repository, EntityManagerInterface $manager, $id=null, $status=null): Response
    {
        if ($id && $status) {
            // modification du statut transmis en paramètre
            // pour la commande d'id transmis en paramètre
            $order = $repository->find($id);
            $order->setStatus($status);
            $manager->persist($order);
            $manager->flush();
            $this->addFlash('success', 'Statut à jour');
            return $this->redirectToRoute('orders');

        }

// récupérationde toutes les commandes par date desc
        $orders = $repository->findBy([], ['date' => 'DESC', 'status' => 'ASC']);

        return $this->render('admin/orders.html.twig', [
            'orders' => $orders,
            'title'=>'Gestion des commandes'
        ]);
    }

    #[Route('/rating_moderation', name:'rating_moderation')]
    #[Route('/publish_edit/{id}', name:'publish_edit')]
    public function rating_moderation(RatingRepository $repository,EntityManagerInterface $manager, $id=null): Response
    {

        if ($id)
        {
            $rating= $repository->find($id);
            $rating->setPublish(!$rating->isPublish());
            $manager->persist($rating);
            $manager->flush();
            return $this->redirectToRoute('rating_moderation');

        }

        $ratings = $repository->findBy([], ['publish'=>'DESC', 'publishDate'=>'DESC']);

        return $this->render('admin/rating_moderation.html.twig', [
            'ratings' => $ratings,
            'title'=>'Modération des avis'
        ]);
    }
    
}
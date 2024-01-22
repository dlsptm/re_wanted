<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route(path: '/security')]
class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/signup', name: 'app_signup')]
     public function signup (Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $pass):Response
    {
        

            // génération de formulaire à partir de la classe userFormType
            $form = $this->createForm(UserType::class);

            
            // ici on va gérer la requête entrante
            $form->handleRequest($request);

            // on va vérifier si le formulaire a été soumis et est valide

            if ($form->isSubmitted() && $form->isValid())
            {
                // instanciation du user
                $user = New User();

                // si le form est soumis et valide
                // set de sa prop active à 0
                // set de son rôle à ROLE_USER

                // on récupère les valeurs du formulaire 
                $user = $form->getData();

            // Encode le mot de passe
            $user->setPassword(
                $pass->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );


                $user->setActive(0);
                $user->setRoles(['ROLE_USER']);
          
             

                // on crée une instance de la classe user à laquelle on passe ces valeurs


                // On persiste les valeurs
                $manager->persist($user);

                // On exécute la transaction
                $manager->flush();

                return $this->redirectToRoute('app_login');
            }


           return $this->render('security/signup.html.twig', [
            'form' => $form->createView(),
           ]);
    }
}
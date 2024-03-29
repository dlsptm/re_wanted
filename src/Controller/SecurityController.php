<?php

namespace App\Controller;

use App\Entity\OrderPurchase;
use App\Entity\User;
use App\Form\NewPasswordType;
use App\Form\UserType;
use App\Repository\OrderPurchaseRepository;
use App\Repository\UserRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
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
  public function signup(
    Request $request,
    EntityManagerInterface $manager,
    UserPasswordHasherInterface $pass,
    EmailService $emailService
  ): Response {

    // génération de formulaire à partir de la classe userFormType
    $form = $this->createForm(UserType::class);


    // ici on va gérer la requête entrante
    $form->handleRequest($request);

    // on va vérifier si le formulaire a été soumis et est valide

    if ($form->isSubmitted() && $form->isValid()) {
      // instanciation du user
      $user = new User();

      // si le form est soumis et valide
      // set de sa prop active à 0
      // set de son rôle à ROLE_USER

      // on récupère les valeurs du formulaire 
      $user = $form->getData();

      // Encode le mot de passe
      $user->setPassword(
        $pass->hashPassword(
          $user,
          $user->getPassword()
        )
      );

      $user->setActive(0);

      // on appelle la méthode generateToken afin de générer une chaine de caratcères aléatoire et unique
      $token = $this->generateToken();

      // on l'affecte à notre utilisateur
      $user->setToken($token);

      // On persiste les valeurs
      $manager->persist($user);

      $userMail = $user->getEmail();


      $emailService->sendEmail(
        $userMail,
        'Activez votre compte',
        '<p>Veuillez clicker sur le lien ci dessous pour confirmer votre inscription</p></p>Si vous n\'etes pas à l\'origine de cette demande, merci de ne pas prendre en considération cet email.',
        'validate_account',
        'Activez mon compte',
        $user,
        $user->getToken(),
        'token',
        $this->getParameter('img_dir')
      );

      // On exécute la transaction
      $manager->flush();

      $this->addFlash('success', 'Votre compte a bien été crée, allez vite l\'activer');

      // on injecte la dépendance mailer


      return $this->redirectToRoute('app_login');
    }


    return $this->render('security/signup.html.twig', [
      'form' => $form->createView(),
    ]);
  }

  private function generateToken()
  {
    // rtrim supprime les espaces en fin de chaine de caractère
    // strtr remplace des occurences dans une chaine ici +/ et -_ (caractères récurent dans l'encodage en base64) par des = pour générer des url valides
    // ce token sera utilisé dans les envoie de mail pour l'activation du compte ou la récupération de mot de passe
    return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
  }


  // Méthode d'entrée au click de validation du compte
  #[Route('/validate-account/{token}', name: 'validate_account')]
  public function validate_account($token, UserRepository $repository, EntityManagerInterface $manager)
  {
    //on requeter un utilisateur sur son token

    $user = $repository->findOneBy(['token' => $token]);

    // si on a un résulta, on passe du coup, sa propriété active à 1 et son token à null, on  persist, execute, et redirige vers la page de connexion

    if ($user) {
      $user->setToken(null);
      $user->setActive(1);
      $manager->persist($user);
      $manager->flush();
      $this->addFlash('success', 'Féliciation, votre compte a bien été activé. Vous pouvez dès à présent vous connecter.');
    } else {
      $this->addFlash('danger', 'Une erreur s\'est produire, merci de rééssayer.');
    }

    return $this->redirectToRoute('app_login');
  }


  // méthode pour reset mot de passe pour accéder au formulaire de la saisie de l'email et généré du coup l'envoi d'un mail de réinitialisation

  #[Route('/reset-password', name: 'reset_password')]
  public function reset_password(Request $request, UserRepository $repository, EntityManagerInterface $manager, EmailService $emailService): Response
  {
  // récupération de la saisie formulaire ->request = $_POST, ->query = $_GET
  $email = $request->request->get('email', '');

  if(!empty($email))
  {
      // requete de user par son email
      $user = $repository->findOneBy(['email' => $email]);

      // si on a utilisateur et que son compte est actif on procède à l'envoie de l'email de récupération
      if($user && $user->getActive() === 1)
      {
          // on génère un token
          $user->setToken($this->generateToken());
          $manager->persist($user);
          $manager->flush();
          $emailService->sendEmail($user->getEmail(), 'Mot de passe perdu?', '<p>Veuillez clicker sur le liens ci-dessous pour réinitaliser votre mot de passe</p><p>Si vous n\'êtes pas l\'origine de cette demande merci de ne pas prendre en considération cet email et nous excuser pour la gêne</p>','new_password', 'Réinitaliser le mot de passe',$user, 'token', $this->getParameter('img_dir'));
           return $this->redirectToRoute('home');
      }
  }

  return $this->render('security/reset_password.html.twig',[
  
  ]);  }

  #[Route('/new-password/{token}', name: 'newpassword')]
  public function newPassword($token, UserRepository $repo, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher)
  {
    // on récupère un user par son token
    $user = $repo->findOneBy(['token' => $token]);

    if($user)
    {
        $form = $this->createForm(NewPasswordType::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // on hash le nouveau mdp
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            // on repasse le token à null
            $user->setToken(null);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', "Votre mot de passe a bien été modifié");
           
            return $this->redirectToRoute('app_login');
      }
    }  
    return $this->render('security/newPassword.html.twig', [
            'form' => $form->createView()
        ]);
}

 // profil de l'utilisateur
 #[IsGranted('IS_AUTHENTICATED_FULLY')]
 #[Route('/profile', name: 'profile')]
 public function profile(): Response
 {
     // il manque ici le traitement du changement d'infos pour mail et nickname


     return $this->render('security/profile.html.twig', [

     ]);
 }

 // page renvoyant à l'utilisateur l'historique de ses commandes ainsi que leur statut de prise en charge
 #[Route('/order_purchases', name: 'order_purchases')]
 #[IsGranted('IS_AUTHENTICATED_FULLY')]
 public function order_purchases(OrderPurchaseRepository $repository): Response
 {

     $orders = $repository->findBy(['user' => $this->getUser()], ['date' => 'DESC']);


     return $this->render('security/order_purchases.html.twig', [
         'orders' => $orders
     ]);
 }

 // page de détail d'une commande
 #[IsGranted('IS_AUTHENTICATED_FULLY')]
 #[Route('/order_detail/{id}', name: 'order_detail')]
 public function order_detail(OrderPurchase $order): Response
 {


     return $this->render('security/order_detail.html.twig', [
         'order' => $order
     ]);
 }

}

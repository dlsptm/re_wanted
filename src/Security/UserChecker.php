<?php
namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;



class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user):void
    {
        // dd(!$user->isActive());
        
        if (!$user instanceof User) {
            return;
        }
        
        if (!$user->getActive() == 1) {
            throw new CustomUserMessageAuthenticationException(
                'votre compte n\'est pas actif. regardez votre boite mail ou contacter un admin'
            );
            
            // $this->addFlash('error', "votre compte n'est pas actif. regardez votre boite mail ou contacter un admin" );
            // return $this->redirectToRoute('app_logout');
        }
        
    }

    public function checkPostAuth(UserInterface $user):void
    {
        $this->checkPreAuth($user);
    }
}
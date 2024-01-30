<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Part\DataPart;

class EmailService
{
  private $mailer;

  public function __construct(MailerInterface $mailer)
  {
    $this->mailer = $mailer;
  }
  

  public function sendEmail($to, $title, $content, $route, $button, $user, $param=null, $paramName=null, $imgDir=null)
  {


    $email = (new TemplatedEmail())
    ->from('dlsptm6981@gmail.com')
    ->to($to)
    ->addPart((new DataPart(fopen($imgDir.'/logo.png','r'), 'logo', 'image/png' ))->asInline())
    ->subject($title)

    // path of the Twig template to render
    ->htmlTemplate('email/validateAccount.html.twig')

    // change locale used in the template, e.g. to match user's locale
    ->locale('fr')

    // pass variables (name => value) to the template
    ->context([
        'user' => $user,
        'content'=>$content,
        'title' => $title,
        'route' => $route,
        'button'=>$button,
        'param'=>$param,
        'paramName'=>$paramName

    ]);   


    // foreach($media as $medias) {
    //   $email->addPart($media);
    // }


    try {
      $this->mailer->send($email); 
    } catch(TransportExceptionInterface $e) {
      dd($e);
    };

  }

  public function newsletter()
  {
    return;
  }

}


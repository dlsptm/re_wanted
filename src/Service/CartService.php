<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

  class CartService
  {

    private $repository;
    private $session;


    /**
     * On crée le constructeur pour injecter automatiquement la session et le repository à l'injection de CartService dans nos controller
     *
     * @param ProductRepository $repository
     * @param RequestStack $session
     */
    public function __construct( ProductRepository $repository,  RequestStack $session)
    {

      $this->repository = $repository;
      $this->session = $session;
    }

    public function add (int $id):void
    {
      $local=$this->session->getSession();
      $cart=$local->get('cart', []);
      // ['id'=> 'quantity']
      // si l'indice $id n'existe pas dans le tableau, le produit n'a pas été encore ajouté on initialise à un
      if (!isset($cart[$id])) {
        $cart[$id]=1;
      } else {
        //on incrémente la quantité
        $cart[$id]++;
      }

      // on met à jour la session
      $local->set('cart', $cart);

    }

    public function remove (int $id):void
    {
      $local=$this->session->getSession();
      $cart=$local->get('cart', []);
     
      if (isset($cart[$id]) && $cart[$id] > 1) 
      {
        //on décrémente la quantité
        $cart[$id]--;
      } else {
        // on supprime totalement l'entrée ayant cet id
        unset($cart[$id]);
      }

      // on met à jour la session
      $local->set('cart', $cart);
    }

    public function delete (int $id):void
    {
      $local=$this->session->getSession();
      $cart=$local->get('cart', []);
      
      // on supprime toutes les entrées
      if (isset($cart[$id])) 
      {

        unset($cart[$id]);


      }

      // on met à jour la session
      $local->set('cart', $cart);
    }

    public function destroy ():void
    {

      $this->session->getSession()->remove('cart');

    }

    public function getCartWithData ():array
    {
      $local=$this->session->getSession();
      $cart=$local->get('cart', []);

      $cartWithData=[];

      foreach($cart as $id => $quantity)
      {
        $cartWithData[]=[
          'product'=>$this->repository->find($id),
          'quantity'=>$quantity
        ];
      }
      return $cartWithData;
    }

    public function getTotal():float
    {

      $total=0;

      foreach ($this->getCartWithData() as $data)
      {
        $total += $data['product']->getPrice() * $data['quantity'];
      }
    return $total;
    }


  }

?>
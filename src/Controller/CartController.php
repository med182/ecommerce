<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;


#[Route("/cart", name:"cart_")]
class CartController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(SessionInterface $session, ProductRepository $productRepository): Response
    {
        $panier = $session->get("panier", []);

        $dataPanier= [];
        $total = 0;

        foreach ($panier as $id => $quantite) {
            $product = $productRepository->find($id);
            $dataPanier[] =[
                "product" => $product,
                "quantite"=>$quantite,
                
            ];
            $total += $product->getPrice() * $quantite;
       
       
        }
        return $this->render('cart/index.html.twig', 
       compact("dataPanier","total")
        );
    }

    #[Route("/add/{id}", name: "add")]
    public function add(Product $product , SessionInterface $session){
    $panier = $session->get("panier", []);
    $id=$product->getId();
    if(!empty($panier[$id])){
        $panier[$id]++;

    }else{
        $panier[$id]= 1;
    }
    $session->set("panier", $panier);

     return $this->redirectToRoute("cart_index");
    }
}

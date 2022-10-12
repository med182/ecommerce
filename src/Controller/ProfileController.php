<?php

namespace App\Controller;

use App\Form\EdithProfileType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/profil', name: 'profile_')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('profile/index.html.twig', [
            'controller_name' => 'Profil de l\'utilisateur',
        ]);
    }
    #[Route('/commandes', name: 'orders')]
    public function orders(): Response
    {
        return $this->render('profile/index.html.twig', [
            'controller_name' => 'commandes l\'utilisateur',
        ]);
    }
   
    #[Route('/modifier', name: 'modifier')]
    public function editProfile (Request $request, EntityManagerInterface $em)
    {   $user=$this->getUser();
        $form=$this->createForm(EdithProfileType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted()&& $form->isValid())
        {
        
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'profile mis a jour');
            return $this->redirectToRoute('profile_index');
        }

        return $this->render('profile/editprofile.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\UserAutenticatorAuthenticator;
use App\Service\JWTService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;


class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function register(Request $request,
     UserPasswordHasherInterface $userPasswordHasher, 
     UserAuthenticatorInterface $userAuthenticator, 
     UserAutenticatorAuthenticator $authenticator, 
     EntityManagerInterface $entityManager, 
     SendMailService $mail, JWTService $jwt): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            $header = [
                'typ'=>'JWT',
                'alg'=> 'HS256'
            ];
             
            
            $payload = [
                'user_id'=> $user->getId()
            ];

            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));
            
   
            $mail->send(
                'no-replay@monsite.net',
                $user->getEmail(),
                'Activation de votre compte e-commerce',
                'register',
                compact('user', 'token')
            );
            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    #[Route('/verif/{token}', name: 'verify_user')]
    public function verifyUser($token, JWTService $jwt, 
    UserRepository $userRepository,
    EntityManagerInterface $manager): Response
    {
        if($jwt->isValid($token) && !$jwt->isExpired($token)
        && $jwt->check($token, $this->getParameter('app.jwtsecret'))){
            $payload = $jwt->getpayload($token);
            $user = $userRepository->find($payload['user_id']);

            if($user && !$user->getIsVerified()){
                $user->setIsVerified(true);
                $manager->flush($user);
                $this->addFlash('success', 'Utilisateur activé');
                return $this->redirectToRoute('profile_index');
            }

        }{
         $this->addFlash('danger', 'Le token est invalide ou a expiré');
         return $this->redirectToRoute('app_login');
        }
    }
    #[Route('/renvoieverif', name: 'resend_verif')]
    public function resendVerif(JWTService $jwt, 
    SendMailService $mail, UserRepository $userRepository ): Response{
        $user=$this->getUser();

        if(!$user){
            $this->addFlash('danger', 'Vous devez etre connecté pour accéder à cette page');
            return $this->redirectToRoute('app_login');
        }
        if($user->getIsVerified()){
            $this->addFlash('warning', 'Cet utilisateur est déjà activé');
            return $this->redirectToRoute('profile_index');
        }

        $header = [
            'typ'=>'JWT',
            'alg'=> 'HS256'
        ];
         
        
        $payload = [
            'user_id'=> $user->getId()
        ];

        $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));
        

        $mail->send(
            'no-replay@monsite.net',
            $user->getEmail(),
            'Activation de votre compte e-commerce',
            'register',
            compact('user', 'token')
        );
        $this->addFlash('success', 'Email de vérification envoyé');
        return $this->redirectToRoute('profile_index');
}
}
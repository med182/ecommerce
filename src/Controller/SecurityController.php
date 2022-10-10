<?php

namespace App\Controller;

use App\Service\SendMailService;
use App\Repository\UserRepository;
use App\Form\ResetPasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ResetPasswordRequestFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/connexion', name: 'app_login')]
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

    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/oubli-pass', name:'forgotten_password')]
    public function forgottenPassword(Request $request,
     UserRepository $userRepository, 
     TokenGeneratorInterface $tokenGeneratorInterface,
     EntityManagerInterface $entityManager,
     SendMailService $mail): Response
    {
     
        $form = $this->createForm(ResetPasswordRequestFormType::class);

        $form->handleRequest($request);
      
        if($form->isSubmitted()&& $form->isValid()){
            $user= $userRepository->findOneByEmail($form->get('email')->getData());
           if($user){
        $token= $tokenGeneratorInterface->generateToken();
        $user->setResetToken($token);
        $entityManager->persist($user);
        $entityManager->flush();

        $url = $this->generateUrl('reset_pass',['token'=>$token], 
        UrlGeneratorInterface::ABSOLUTE_URL);
      
        $context = compact('url', 'user');

        $mail->send(
            'noreplay@e-commerce.fr',
            $user->getEmail(),
            'Réinitialisation de mot de passe',
            'password_reset',
            $context
        );
        $this->addFlash('success', 'Email envoyé avec succès');
        return $this->redirectToRoute('app_login');
        }
        $this->addFlash('danger','Un problème est survenu');
        return $this->redirectToRoute('app_login');


        }
        return $this->render('security/reset_password_request.html.twig',[
            'requestPassForm'=>$form->createView()
        ]);

    }


    #[Route('/oubli-pass/{token}', name: 'reset_pass')]
    public function resetPass (string $token,
    Request $request,
    UserRepository $userRepository,
    EntityManagerInterface $entityManager,
    UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $userRepository->findOneByResetToken($token);
        if($user){
          $form = $this->createForm(ResetPasswordFormType::class);

          $form->handleRequest($request);

          if($form->isSubmitted() && $form->isValid()){
            $user->setResetToken('');
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
                );
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Mot de passe changé avec succès');
          }
          return $this->render('security/reset_password.html.twig',[
            'passForm' =>$form->createView()
          ]);
        }
        $this->addFlash('danger', 'Jeton invalide');
        return $this->redirectToRoute('app_login');
    }

}

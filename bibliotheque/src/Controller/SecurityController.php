<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ResetPasswordForm;
use App\Entity\User;

class SecurityController extends AbstractController
{
    #[Route(path: '/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {

        if($this->getUser()) {
            if (in_array('ROLE_BANNED', $this->getUser()->getRoles(), true)) {
                $this->addFlash('error', 'Votre compte est banni. Veuillez contacter l\'administration.');
                $this->container->get('security.token_storage')->setToken(null);
                $this->container->get('request_stack')->getSession()->invalidate();
                $response = $this->redirectToRoute('banned');
                $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
                $response->headers->set('Pragma', 'no-cache');
                $response->headers->set('Expires', '0');
                return $response;

            }
        }
        
        

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }


    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        
    }
}





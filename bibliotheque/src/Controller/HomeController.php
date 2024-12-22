<?php

namespace App\Controller;

use App\Repository\LivreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function index(LivreRepository $livreRepository): Response
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
        // Récupérer tous les livres de la base de données
        $livres = $livreRepository->getAllLivres();

        return $this->render('home/index.html.twig', [
            'livres' => $livres,
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/', name: 'main')]
    public function login(LivreRepository $livreRepository): Response
    {
        $livres = $livreRepository->findLatestBooks(5);
        return $this->render('main/accueil.html.twig', [
            'livres' => $livres,
        ]);
    }

    #[Route('/banned', name: 'banned')]
    public function banned(): Response
    {

        return $this->render('error/banned.html.twig');
    }
}

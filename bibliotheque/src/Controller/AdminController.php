<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use App\Repository\LivreRepository;
use App\Entity\User;
use App\Entity\Livre;
use App\Entity\Genre;
use App\Entity\Auteur;
use Doctrine\ORM\EntityManagerInterface;  // Import the EntityManagerInterface

class AdminController extends AbstractController
{
    private $entityManager;

    // Inject EntityManagerInterface into the controller constructor
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/admin/dashboard', name: 'app_admin')]
    public function index(UserRepository $userRepository, LivreRepository $livreRepository): Response
    {
        // Fetch all users and books
        $users = $userRepository->findAll();
        $livres = $livreRepository->findAll();

        return $this->render('admin/dashboard.html.twig', [
            'users' => $users,
            'livres' => $livres,
        ]);
    }

    #[Route("/admin/delete/user/{id}", name:"admin_delete_user")]
    public function deleteUser(User $user): Response
    {
        // Use the injected EntityManager to remove the user
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $this->addFlash('success', 'User deleted successfully');

        return $this->redirectToRoute('app_admin');
    }

    #[Route("/admin/delete/book/{id}", name:"admin_delete_book")]
    public function deleteBook(Livre $livre): Response
    {
        // Use the injected EntityManager to remove the book
        $this->entityManager->remove($livre);
        $this->entityManager->flush();

        $this->addFlash('success', 'Book deleted successfully');

        return $this->redirectToRoute('app_admin');
    }

    #[Route("/admin/edit/user/{id}", name:"admin_edit_user")]
    public function editUser(Request $request, User $user): Response
    {
        if ($request->isMethod('POST')) {
            // Update user data (excluding password)
            $user->setUsername($request->get('username'));
            $user->setEmail($request->get('email'));

            $this->entityManager->flush();

            $this->addFlash('success', 'User updated successfully');
            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/edit_user.html.twig', [
            'user' => $user
        ]);
    }

    #[Route("/admin/ban/user/{id}", name:"admin_ban_user")]
    public function banUser(User $user): Response
    {
        // Ban user
        $user->setIsBanned(true);
        $this->entityManager->flush();

        $this->addFlash('success', 'User banned successfully');
        return $this->redirectToRoute('app_admin');
    }

    #[Route("/admin/unban/user/{id}", name:"admin_unban_user")]
    public function unbanUser(User $user): Response
{
    // Remove the 'ROLE_BANNED' role from the user
    $user->setIsBanned(false);

    // Persist changes
    $this->entityManager->flush();

    $this->addFlash('success', 'User unbanned successfully');

    return $this->redirectToRoute('app_admin');
}

    #[Route("/admin/edit/book/{id}", name:"admin_edit_book")]
    public function editBook(Request $request, Livre $livre): Response
    {
        if ($request->isMethod('POST')) {
            // Update book data
            $livre->setTitre($request->get('titre'));
            $livre->setGenre($request->get('genre'));
            $livre->setResume($request->get('resume'));

            $this->entityManager->flush();

            $this->addFlash('success', 'Book updated successfully');
            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/edit_book.html.twig', [
            'livre' => $livre
        ]);
    }

    // Route pour ajouter un livre
    #[Route("/admin/add/book", name:"admin_add_book")]
    public function addBook(Request $request): Response
    {
        $livre = new Livre();

        // On récupère les genres et auteurs pour les afficher dans le formulaire
        $genres = $this->entityManager->getRepository(Genre::class)->findAll();
        $auteurs = $this->entityManager->getRepository(Auteur::class)->findAll();

        // Formulaire de traitement
        if ($request->isMethod('POST')) {
            $livre->setTitre($request->get('titre'));
            $livre->setResume($request->get('resume'));
            $livre->setDatePublication(new \DateTime($request->get('datePublication')));
            $livre->setGenre($this->entityManager->getRepository(Genre::class)->find($request->get('genre')));
            $livre->setAuteur($this->entityManager->getRepository(Auteur::class)->find($request->get('auteur')));
            $livre->setImage($request->get('image'));

            $this->entityManager->persist($livre);
            $this->entityManager->flush();

            $this->addFlash('success', 'Livre ajouté avec succès');
            return $this->redirectToRoute('app_admin');
        }

        return $this->render('livre/add_book.html.twig', [
            'genres' => $genres,
            'auteurs' => $auteurs
        ]);
    }
}

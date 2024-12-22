<?php
// src/Controller/UserController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Form\ResetPasswordForm;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Form\UpdateUserForm;

class UserController extends AbstractController
{
    private $entityManager;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    // src/Controller/UserController.php

    #[Route('/profile', name: 'user_account')]
    public function index(): Response
    {

        return $this->render('user/profile.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

#[Route('/profile/edit', name: 'user_edit_account')]
public function edit(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordEncoder): Response
{
    // Récupérer l'utilisateur actuellement connecté
    $user = $this->getUser();

    // Créer le formulaire de mise à jour de l'utilisateur
    $form = $this->createForm(UpdateUserForm::class, $user);

    // Traiter la soumission du formulaire
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Si un mot de passe a été soumis, le hacher avant de le sauvegarder
        $newPassword = $form->get('password')->getData();
        if ($newPassword) {
            // Hacher le mot de passe
            $encodedPassword = $passwordEncoder->hashPassword($user, $newPassword);
            $user->setPassword($encodedPassword);
        }

        // Sauvegarder les modifications dans la base de données
        $entityManager->persist($user);
        $entityManager->flush();

        // Afficher un message de succès
        $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

        // Rediriger pour éviter la soumission multiple du formulaire
        return $this->redirectToRoute('user_account');
    }

    // Rendre la vue avec le formulaire
        return $this->render('user/edit_account.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/reset', name: 'mot_de_passe_oublie')]
    public function editPassword(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordEncoder): Response
    {
        // Créer le formulaire de mise à jour du mot de passe
        $form = $this->createForm(ResetPasswordForm::class);

        // Traiter la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les données du formulaire
            $email = $form->get('email')->getData();
            $oldPassword = $form->get('old_password')->getData();
            $newPassword = $form->get('new_password')->getData();

            // Vérifier si l'utilisateur existe avec cet email
            $user = $entityManager->getRepository(User::class)->findOneByEmail($email);

            if (!$user) {
                // Si l'utilisateur n'existe pas, afficher un message d'erreur
                $this->addFlash('error', 'Aucun utilisateur trouvé avec cet email.');
                return $this->redirectToRoute('mot_de_passe_oublie');
            }

            // Vérifier si le mot de passe actuel est valide
            if (!$passwordEncoder->isPasswordValid($user, $oldPassword)) {
                // Si le mot de passe actuel est incorrect, afficher un message d'erreur
                $this->addFlash('error', 'L\'ancien mot de passe est incorrect.');
                return $this->redirectToRoute('mot_de_passe_oublie');
            }

            // Hacher le nouveau mot de passe
            $encodedPassword = $passwordEncoder->hashPassword($user, $newPassword);
            $user->setPassword($encodedPassword);

            // Sauvegarder les modifications dans la base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Afficher un message de succès
            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');

            // Rediriger pour éviter la soumission multiple du formulaire
            return $this->redirectToRoute('user_account');
        }

        // Rendre la vue avec le formulaire
        return $this->render('user/forgot_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    
}

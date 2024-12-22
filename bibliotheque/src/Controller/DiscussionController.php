<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Livre;
use App\Entity\Discussion;
use App\Form\DiscussionType;

class DiscussionController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    #[Route('/discussion/ajouter/{livreId}', name: 'add_discussion')]
    public function addDiscussion(int $livreId, Request $request): Response
    {
        $livre = $this->entityManager->getRepository(Livre::class)->find($livreId);
        if (!$livre) {
            throw $this->createNotFoundException('Livre non trouvé.');
        }

        $discussion = new Discussion();
        $discussion->setLivre($livre);
        $discussion->setAuteur($this->getUser()); // Utilisateur connecté
        
        $form = $this->createForm(DiscussionType::class, $discussion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($discussion);
            $this->entityManager->flush();

            $this->addFlash('success', 'Discussion ajoutée avec succès');
            return $this->redirectToRoute('livre_show', ['id' => $livreId]); // Rediriger vers la page du livre
        }

        return $this->render('discussion/add.html.twig', [
            'form' => $form->createView(),
            'livre' => $livre,
        ]);
    }

}

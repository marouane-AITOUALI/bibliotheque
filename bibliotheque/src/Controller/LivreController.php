<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Entity\Discussion;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\DiscussionType;
use Doctrine\ORM\EntityManagerInterface;

class LivreController extends AbstractController
{
    #[Route('/livre/{id}', name: 'livre_show')]
    public function detailsLivre(Livre $livre): Response
    {
        return $this->render('livre/show.html.twig', [
            'livre' => $livre,
        ]);
    }

    public function editDiscussion(Livre $livre, Discussion $discussion, Request $request, EntityManagerInterface $entityManager): Response
{
    // Ensure the current user is the author of the discussion
    if ($discussion->getAuteur() !== $this->getUser()) {
        throw $this->createAccessDeniedException('You cannot modify this comment.');
    }

    $form = $this->createForm(DiscussionType::class, $discussion);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();

        return $this->redirectToRoute('livre_show', ['id' => $livre->getId()]);
    }

    return $this->render('livre/edit_discussion.html.twig', [
        'livre' => $livre,
        'discussion' => $discussion,
        'form' => $form->createView(),
    ]);
}

/**
 * @Route("/livre/{id}/discussion/delete/{discussionId}", name="delete_discussion")
 */
    #[Route("/livre/{id}/discussion/delete/{discussionId}", name:"delete_discussion")]
    public function deleteDiscussion(Livre $livre, Discussion $discussion, EntityManagerInterface $entityManager): Response
    {
        // Ensure the current user is the author of the discussion
        if ($discussion->getAuteur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot delete this comment.');
        }

        $entityManager->remove($discussion);
        $entityManager->flush();

        return $this->redirectToRoute('livre_show', ['id' => $livre->getId()]);
    }

}

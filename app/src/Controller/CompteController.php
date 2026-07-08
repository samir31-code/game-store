<?php
namespace App\Controller;

use App\Entity\Adresse;
use App\Form\AdresseType;
use App\Entity\DemandeAssistance;
use App\Form\DemandeAssistanceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\DemandeAssistanceRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// Importation nécessaire pour vider la session de l'utilisateur lors de la suppression
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/compte')]
#[IsGranted('ROLE_USER')]
class CompteController extends AbstractController
{
    // ── Mon compte ───────────────────────────────────────────────
    #[Route('/', name: 'compte_index')]
    public function index(
        DemandeAssistanceRepository $demandeRepository
    ): Response {
        return $this->render('compte/index.html.twig', [
            'user'         => $this->getUser(),
            'mes_demandes' => $demandeRepository->findBy(
                ['client' => $this->getUser()],
                ['dateCreation' => 'DESC']
            ),
        ]);
    }

    // ── Supprimer le compte définitivement (RGPD) ────────────────
    #[Route('/supprimer', name: 'compte_supprimer', methods: ['POST'])]
    public function supprimerCompte(
        Request $request,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    ): Response {
        $user = $this->getUser();

        // Utilisation de la même syntaxe de validation de token que vos autres méthodes
        if ($this->isCsrfTokenValid('delete_account_' . $user->getId(), $request->getPayload()->getString('_token'))) {

            // 1. Déconnexion manuelle et destruction de la session en cours
            $tokenStorage->setToken(null);
            $request->getSession()->invalidate();

            // 2. Suppression de l'utilisateur en BDD
            $em->remove($user);
            $em->flush();

            $this->addFlash('success', 'Votre compte et l\'intégralité de vos données ont été définitivement supprimés.');
            return $this->redirectToRoute('app_home');
        }

        $this->addFlash('danger', 'Une erreur de sécurité est survenue. Impossible de supprimer le compte.');
        return $this->redirectToRoute('compte_index');
    }

    // ── Créer une demande d'assistance ───────────────────────────
    #[Route('/assistance/nouvelle', name: 'compte_assistance_new', methods: ['GET', 'POST'])]
    public function nouvelleAssistance(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $demande = new DemandeAssistance();
        $form    = $this->createForm(DemandeAssistanceType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $demande->setClient($this->getUser());
            $em->persist($demande);
            $em->flush();
            $this->addFlash('success', 'Demande d\'assistance envoyée ! Nous vous répondrons rapidement.');
            return $this->redirectToRoute('compte_index');
        }

        return $this->render('compte/assistance_new.html.twig', [
            'form' => $form,
        ]);
    }

    // ── Supprimer une demande d'assistance résolue ───────────────
    #[Route('/assistance/{id}/supprimer', name: 'compte_assistance_delete', methods: ['POST'])]
    public function supprimerAssistance(
        Request $request,
        DemandeAssistance $demande,
        EntityManagerInterface $em
    ): Response {
        if ($demande->getClient() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($demande->getStatut() !== 'resolu') {
            $this->addFlash('danger', 'Vous ne pouvez supprimer que les demandes résolues.');
            return $this->redirectToRoute('compte_index');
        }

        if ($this->isCsrfTokenValid('delete' . $demande->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($demande);
            $em->flush();
            $this->addFlash('success', 'Demande d\'assistance supprimée.');
        }

        return $this->redirectToRoute('compte_index');
    }

    // ── Liste des adresses ───────────────────────────────────────
    #[Route('/adresses', name: 'compte_adresse_index')]
    public function adresseIndex(): Response
    {
        return $this->render('compte/adresse/index.html.twig', [
            'adresses' => $this->getUser()->getAdresses(),
        ]);
    }

    // ── Ajouter une adresse ──────────────────────────────────────
    #[Route('/adresses/nouvelle', name: 'compte_adresse_new', methods: ['GET', 'POST'])]
    public function adresseNew(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $adresse = new Adresse();
        $form    = $this->createForm(AdresseType::class, $adresse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adresse->setUser($this->getUser());
            $em->persist($adresse);
            $em->flush();
            $this->addFlash('success', 'Adresse ajoutée avec succès !');
            return $this->redirectToRoute('compte_adresse_index');
        }

        return $this->render('compte/adresse/new.html.twig', [
            'form' => $form,
        ]);
    }

    // ── Modifier une adresse ─────────────────────────────────────
    #[Route('/adresses/{id}/modifier', name: 'compte_adresse_edit', methods: ['GET', 'POST'])]
    public function adresseEdit(
        Adresse $adresse,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if ($adresse->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(AdresseType::class, $adresse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Adresse modifiée avec succès !');
            return $this->redirectToRoute('compte_adresse_index');
        }

        return $this->render('compte/adresse/edit.html.twig', [
            'adresse' => $adresse,
            'form'    => $form,
        ]);
    }

    // ── Supprimer une adresse ────────────────────────────────────
    #[Route('/adresses/{id}/supprimer', name: 'compte_adresse_delete', methods: ['POST'])]
    public function adresseDelete(
        Request $request,
        Adresse $adresse,
        EntityManagerInterface $em
    ): Response {
        if ($adresse->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete' . $adresse->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($adresse);
            $em->flush();
            $this->addFlash('success', 'Adresse supprimée.');
        }

        return $this->redirectToRoute('compte_adresse_index');
    }
}

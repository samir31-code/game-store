<?php
namespace App\Controller;

use App\Entity\Page;
use App\Entity\User;
use App\Form\PageType;
use App\Entity\Message;
use App\Entity\Etiquette;
use App\Form\MessageType;
use App\Form\EtiquetteType;
use App\Entity\CategorieProduit;
use App\Form\UserManagementType;
use App\Entity\DemandeAssistance;
use App\Form\CategorieProduitType;
use App\Repository\PageRepository;
use App\Repository\UserRepository;
use App\Form\ReponseAssistanceType;
use App\Repository\MessageRepository;
use App\Repository\EtiquetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CategorieProduitRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\DemandeAssistanceRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/webmaster')]
#[IsGranted('ROLE_WEBMASTER')]
class WebmasterController extends AbstractController
{
    // ── Tableau de bord ──────────────────────────────────────────
    #[Route('/', name: 'webmaster_index')]
    public function index(
        CategorieProduitRepository $categorieRepository,
        EtiquetteRepository $etiquetteRepository,
        PageRepository $pageRepository,
        UserRepository $userRepository
    ): Response {
        return $this->render('webmaster/index.html.twig', [
            'nb_categories' => count($categorieRepository->findAll()),
            'nb_etiquettes' => count($etiquetteRepository->findAll()),
            'nb_pages'      => count($pageRepository->findAll()),
            'nb_users'      => count($userRepository->findAll()),
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // CATÉGORIES
    // ══════════════════════════════════════════════════════════════

    #[Route('/categories', name: 'webmaster_categorie_index')]
    public function categorieIndex(
        CategorieProduitRepository $repo
    ): Response {
        return $this->render('webmaster/categorie/index.html.twig', [
            'categories' => $repo->findAll(),
        ]);
    }

    #[Route('/categories/nouveau', name: 'webmaster_categorie_new', methods: ['GET', 'POST'])]
    public function categorieNew(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $categorie = new CategorieProduit();
        $form      = $this->createForm(CategorieProduitType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($categorie);
            $em->flush();
            $this->addFlash('success', 'Catégorie créée avec succès !');
            return $this->redirectToRoute('webmaster_categorie_index');
        }

        return $this->render('webmaster/categorie/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/categories/{id}/modifier', name: 'webmaster_categorie_edit', methods: ['GET', 'POST'])]
    public function categorieEdit(
        CategorieProduit $categorie,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(CategorieProduitType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Catégorie modifiée avec succès !');
            return $this->redirectToRoute('webmaster_categorie_index');
        }

        return $this->render('webmaster/categorie/edit.html.twig', [
            'categorie' => $categorie,
            'form'      => $form,
        ]);
    }

    #[Route('/categories/{id}/supprimer', name: 'webmaster_categorie_delete', methods: ['POST'])]
    public function categorieDelete(
        Request $request,
        CategorieProduit $categorie,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $categorie->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($categorie);
            $em->flush();
            $this->addFlash('success', 'Catégorie supprimée.');
        }
        return $this->redirectToRoute('webmaster_categorie_index');
    }

    // ══════════════════════════════════════════════════════════════
    // ÉTIQUETTES
    // ══════════════════════════════════════════════════════════════

    #[Route('/etiquettes', name: 'webmaster_etiquette_index')]
    public function etiquetteIndex(
        EtiquetteRepository $repo
    ): Response {
        return $this->render('webmaster/etiquette/index.html.twig', [
            'etiquettes' => $repo->findAll(),
        ]);
    }

    #[Route('/etiquettes/nouveau', name: 'webmaster_etiquette_new', methods: ['GET', 'POST'])]
    public function etiquetteNew(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $etiquette = new Etiquette();
        $form      = $this->createForm(EtiquetteType::class, $etiquette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($etiquette);
            $em->flush();
            $this->addFlash('success', 'Étiquette créée avec succès !');
            return $this->redirectToRoute('webmaster_etiquette_index');
        }

        return $this->render('webmaster/etiquette/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/etiquettes/{id}/modifier', name: 'webmaster_etiquette_edit', methods: ['GET', 'POST'])]
    public function etiquetteEdit(
        Etiquette $etiquette,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(EtiquetteType::class, $etiquette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Étiquette modifiée avec succès !');
            return $this->redirectToRoute('webmaster_etiquette_index');
        }

        return $this->render('webmaster/etiquette/edit.html.twig', [
            'etiquette' => $etiquette,
            'form'      => $form,
        ]);
    }

    #[Route('/etiquettes/{id}/supprimer', name: 'webmaster_etiquette_delete', methods: ['POST'])]
    public function etiquetteDelete(
        Request $request,
        Etiquette $etiquette,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $etiquette->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($etiquette);
            $em->flush();
            $this->addFlash('success', 'Étiquette supprimée.');
        }
        return $this->redirectToRoute('webmaster_etiquette_index');
    }

    // ══════════════════════════════════════════════════════════════
    // PAGES / CONTENU MERCANTILE
    // ══════════════════════════════════════════════════════════════

    #[Route('/pages', name: 'webmaster_page_index')]
    public function pageIndex(
        PageRepository $repo
    ): Response {
        return $this->render('webmaster/page/index.html.twig', [
            'pages' => $repo->findAll(),
        ]);
    }

    #[Route('/pages/nouveau', name: 'webmaster_page_new', methods: ['GET', 'POST'])]
    public function pageNew(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $page = new Page();
        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($page);
            $em->flush();
            $this->addFlash('success', 'Page créée avec succès !');
            return $this->redirectToRoute('webmaster_page_index');
        }

        return $this->render('webmaster/page/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/pages/{id}/modifier', name: 'webmaster_page_edit', methods: ['GET', 'POST'])]
    public function pageEdit(
        Page $page,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Page modifiée avec succès !');
            return $this->redirectToRoute('webmaster_page_index');
        }

        return $this->render('webmaster/page/edit.html.twig', [
            'page' => $page,
            'form' => $form,
        ]);
    }

    #[Route('/pages/{id}/supprimer', name: 'webmaster_page_delete', methods: ['POST'])]
    public function pageDelete(
        Request $request,
        Page $page,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $page->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($page);
            $em->flush();
            $this->addFlash('success', 'Page supprimée.');
        }
        return $this->redirectToRoute('webmaster_page_index');
    }

    // ══════════════════════════════════════════════════════════════
    // UTILISATEURS / COMMUNICATION CLIENT
    // ══════════════════════════════════════════════════════════════

    #[Route('/utilisateurs', name: 'webmaster_user_index')]
    public function userIndex(
        UserRepository $repo,
        DemandeAssistanceRepository $demandeRepository
    ): Response {
        return $this->render('webmaster/user/index.html.twig', [
            'users'       => $repo->findAll(),
            'nb_ouvertes' => count($demandeRepository->findBy(['statut' => 'ouvert'])),
            'nb_en_cours' => count($demandeRepository->findBy(['statut' => 'en_cours'])),
            'nb_resolues' => count($demandeRepository->findBy(['statut' => 'resolu'])),
        ]);
    }

    #[Route('/utilisateurs/{id}/modifier', name: 'webmaster_user_edit', methods: ['GET', 'POST'])]
    public function userEdit(
        User $user,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        // Empêcher de modifier son propre compte
        if ($user === $this->getUser()) {
            $this->addFlash('danger', 'Vous ne pouvez pas modifier votre propre compte.');
            return $this->redirectToRoute('webmaster_user_index');
        }

        // Empêcher de modifier les comptes avec des rôles supérieurs
        $rolesInterdits = ['ROLE_WEBMASTER', 'ROLE_GESTIONNAIRE_COMMANDE', 'ROLE_ADMIN_PRODUIT', 'ROLE_ADMIN'];
        foreach ($rolesInterdits as $role) {
            if (in_array($role, $user->getRoles())) {
                $this->addFlash('danger', 'Vous ne pouvez pas modifier ce compte.');
                return $this->redirectToRoute('webmaster_user_index');
            }
        }

        $form = $this->createForm(UserManagementType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Utilisateur modifié avec succès !');
            return $this->redirectToRoute('webmaster_user_index');
        }

        return $this->render('webmaster/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/utilisateurs/{id}/supprimer', name: 'webmaster_user_delete', methods: ['POST'])]
    public function userDelete(
        Request $request,
        User $user,
        EntityManagerInterface $em
    ): Response {
        // Empêcher de supprimer son propre compte
        if ($user === $this->getUser()) {
            $this->addFlash('danger', 'Vous ne pouvez pas supprimer votre propre compte.');
            return $this->redirectToRoute('webmaster_user_index');
        }

        // Empêcher de supprimer les comptes avec des rôles supérieurs
        $rolesInterdits = ['ROLE_WEBMASTER', 'ROLE_GESTIONNAIRE_COMMANDE', 'ROLE_ADMIN_PRODUIT', 'ROLE_ADMIN'];
        foreach ($rolesInterdits as $role) {
            if (in_array($role, $user->getRoles())) {
                $this->addFlash('danger', 'Vous ne pouvez pas supprimer ce compte.');
                return $this->redirectToRoute('webmaster_user_index');
            }
        }

        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur supprimé.');
        }

        return $this->redirectToRoute('webmaster_user_index');
    }

    // ══════════════════════════════════════════════════════════════
    // MESSAGES — Envoyer un message à un client
    // ══════════════════════════════════════════════════════════════

    #[Route('/messages', name: 'webmaster_message_index')]
    public function messageIndex(MessageRepository $repo): Response
    {
        return $this->render('webmaster/message/index.html.twig', [
            // Messages envoyés par le webmaster
            'messages_envoyes' => $repo->findBy(
                ['expediteur' => $this->getUser()],
                ['dateEnvoi' => 'DESC']
            ),
        ]);
    }

    #[Route('/messages/envoyer/{id}', name: 'webmaster_message_envoyer', methods: ['GET', 'POST'])]
    public function messageEnvoyer(
        User $destinataire,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        // Vérifier que le destinataire est bien un client
        if (!in_array('ROLE_CLIENT', $destinataire->getRoles())
            || in_array('ROLE_WEBMASTER', $destinataire->getRoles())
            || in_array('ROLE_ADMIN', $destinataire->getRoles())) {
            $this->addFlash('danger', 'Vous ne pouvez envoyer des messages qu\'aux clients.');
            return $this->redirectToRoute('webmaster_user_index');
        }

        $message = new Message();
        $form    = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message->setExpediteur($this->getUser());
            $message->setDestinataire($destinataire);
            $em->persist($message);
            $em->flush();
            $this->addFlash('success', 'Message envoyé à ' . $destinataire->getPrenom() . ' ' . $destinataire->getNom() . ' !');
            return $this->redirectToRoute('webmaster_message_index');
        }

        return $this->render('webmaster/message/envoyer.html.twig', [
            'form'         => $form,
            'destinataire' => $destinataire,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // DEMANDES D'ASSISTANCE
    // ══════════════════════════════════════════════════════════════

    #[Route('/assistance', name: 'webmaster_assistance_index')]
    public function assistanceIndex(
        Request $request,
        DemandeAssistanceRepository $repo
    ): Response {
        // Récupérer le filtre de statut depuis l'URL (?statut=ouvert)
        $statut = $request->query->get('statut');

        return $this->render('webmaster/assistance/index.html.twig', [
            'demandes_ouvertes' => $repo->findBy(['statut' => 'ouvert'],   ['dateCreation' => 'DESC']),
            'demandes_en_cours' => $repo->findBy(['statut' => 'en_cours'], ['dateCreation' => 'DESC']),
            'demandes_resolues' => $repo->findBy(['statut' => 'resolu'],   ['dateCreation' => 'DESC']),
            'statut_actif'      => $statut, // Pour mettre en évidence la section active
        ]);
    }

    #[Route('/assistance/{id}', name: 'webmaster_assistance_show', methods: ['GET', 'POST'])]
    public function assistanceShow(
        DemandeAssistance $demande,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(ReponseAssistanceType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $demande->setDateReponse(new \DateTimeImmutable());
            $em->flush();
            $this->addFlash('success', 'Réponse envoyée avec succès !');
            return $this->redirectToRoute('webmaster_assistance_index');
        }

        return $this->render('webmaster/assistance/show.html.twig', [
            'demande' => $demande,
            'form'    => $form,
        ]);
    }

    #[Route('/assistance/{id}/supprimer', name: 'webmaster_assistance_delete', methods: ['POST'])]
    public function assistanceDelete(
        Request $request,
        DemandeAssistance $demande,
        EntityManagerInterface $em
    ): Response {
        // Vérifier que la demande est bien résolue
        if ($demande->getStatut() !== 'resolu') {
            $this->addFlash('danger', 'Vous ne pouvez supprimer que les demandes résolues.');
            return $this->redirectToRoute('webmaster_assistance_index');
        }

        if ($this->isCsrfTokenValid('delete' . $demande->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($demande);
            $em->flush();
            $this->addFlash('success', 'Demande d\'assistance supprimée.');
        }

        return $this->redirectToRoute('webmaster_assistance_index');
    }
}

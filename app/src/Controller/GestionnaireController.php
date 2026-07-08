<?php
namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\User;
use App\Entity\Facture;
use App\Entity\Commande;
use App\Entity\Etiquette;
use App\Form\EtiquetteType;
use App\Repository\ProduitRepository;
use App\Repository\CommandeRepository;
use App\Repository\EtiquetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/gestionnaire')]
#[IsGranted('ROLE_GESTIONNAIRE_COMMANDE')]
class GestionnaireController extends AbstractController
{
    // ── Tableau de bord ──────────────────────────────────────────
    #[Route('/', name: 'gestionnaire_index')]
    public function index(CommandeRepository $repo): Response
    {
        // 🎯 CORRECTION : On retire l'exclusion du panierSauvegarde pour voir les paniers en attente
        $commandesAttente = $repo->createQueryBuilder('c')
            ->where('c.paiementValide = false OR c.paiementValide IS NULL')
            ->orderBy('c.dateCommande', 'DESC')
            ->getQuery()
            ->getResult();

        // Requête pour les commandes validées (elles restent inchangées, un panier payé n'est plus un panier temporaire)
        $commandesValidees = $repo->createQueryBuilder('c')
            ->where('c.paiementValide = true')
            ->andWhere('c.panierSauvegarde = false OR c.panierSauvegarde IS NULL')
            ->andWhere('c.statutExpedition IS NULL OR c.statutExpedition = :statut')
            ->setParameter('statut', 'en_preparation')
            ->orderBy('c.dateCommande', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('gestionnaire/index.html.twig', [
            'commandes_attente'  => $commandesAttente,
            'commandes_validees' => $commandesValidees,
        ]);
    }

    // ── Liste toutes les commandes ───────────────────────────────
    #[Route('/commandes', name: 'gestionnaire_commande_index')]
    public function commandeIndex(
        Request $request,
        CommandeRepository $repo
    ): Response {
        $filtre = $request->query->get('filtre');

        if ($filtre === 'attente') {
            // 🎯 CORRECTION : Permet d'afficher les paniers des utilisateurs connectés dans l'index filtré
            $commandes = $repo->createQueryBuilder('c')
                ->where('c.paiementValide = false OR c.paiementValide IS NULL')
                ->orderBy('c.dateCommande', 'DESC')
                ->getQuery()
                ->getResult();
            $titre = '⏳ Commandes et Paniers en attente de paiement';

        } elseif ($filtre === 'validees') {
            $commandes = $repo->createQueryBuilder('c')
                ->where('c.paiementValide = true')
                ->andWhere('c.panierSauvegarde = false OR c.panierSauvegarde IS NULL')
                ->andWhere('c.statutExpedition IS NULL OR c.statutExpedition = :statut')
                ->setParameter('statut', 'en_preparation')
                ->orderBy('c.dateCommande', 'DESC')
                ->getQuery()
                ->getResult();
            $titre = '✅ Commandes payées à expédier';

        } else {
            // 🎯 CORRECTION : "Toutes les commandes" englobe désormais absolument tout (paniers inclus)
            $commandes = $repo->createQueryBuilder('c')
                ->orderBy('c.dateCommande', 'DESC')
                ->getQuery()
                ->getResult();
            $titre = '📋 Toutes les commandes (Paniers inclus)';
        }

        return $this->render('gestionnaire/commande/index.html.twig', [
            'commandes' => $commandes,
            'titre'     => $titre,
            'filtre'    => $filtre,
        ]);
    }

    // ── Supprimer une commande en attente ─────────────────────────
    #[Route('/commandes/{id}/supprimer', name: 'gestionnaire_commande_delete', methods: ['POST'])]
    public function commandeDelete(
        Request $request,
        Commande $commande,
        EntityManagerInterface $em
    ): Response {
        if ($commande->isPaiementValide()) {
            $this->addFlash('danger', 'Impossible de supprimer une commande déjà payée.');
            return $this->redirectToRoute('gestionnaire_commande_show', ['id' => $commande->getId()]);
        }

        if ($this->isCsrfTokenValid('delete_commande' . $commande->getId(), $request->request->get('_token'))) {
            $em->remove($commande);
            $em->flush();
            $this->addFlash('success', 'La commande a été supprimée avec succès.');
        }

        return $this->redirectToRoute('gestionnaire_commande_index', ['filtre' => 'attente']);
    }

    // ── Détail d'une commande ────────────────────────────────────
    #[Route('/commandes/{id}', name: 'gestionnaire_commande_show')]
    public function commandeShow(Commande $commande, ProduitRepository $produitRepo): Response
    {
        $produitsPanier =[];

        if ($commande->getPanier()) {
            foreach ($commande->getPanier() as $id => $quantite) {
                $produit = $produitRepo->find($id);
                if ($produit) {
                    $produitsPanier[] = [
                        'produit'  => $produit,
                        'quantite' => $quantite
                    ];
                }
            }
        }

        return $this->render('gestionnaire/commande/show.html.twig', [
            'commande'        => $commande,
            'produits_panier' => $produitsPanier,
        ]);
    }

    // ── Détail d'un client ────────────────────────────────────────
    #[Route('/client/{id}', name: 'gestionnaire_client_show', methods: ['GET'])]
    public function clientShow(User $client): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'client' => $client,
        ]);
    }

    // ── Valider le paiement ──────────────────────────────────────
    #[Route('/commandes/{id}/valider-paiement', name: 'gestionnaire_commande_paiement', methods: ['POST'])]
    public function validerPaiement(
        Request $request,
        Commande $commande,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('paiement' . $commande->getId(), $request->getPayload()->getString('_token'))) {
            // Si le gestionnaire valide manuellement, ce n'est plus un panier temporaire
            $commande->setPaiementValide(true);
            $commande->setPanierSauvegarde(false);
            $em->flush();
            $this->addFlash('success', 'Paiement validé pour la commande ' . $commande->getNumeroCommande());
        }
        return $this->redirectToRoute('gestionnaire_commande_show', ['id' => $commande->getId()]);
    }

    // ── Générer une facture ──────────────────────────────────────
    #[Route('/commandes/{id}/facture', name: 'gestionnaire_facture_generer', methods: ['POST'])]
    public function genererFacture(
        Request $request,
        Commande $commande,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('facture' . $commande->getId(), $request->getPayload()->getString('_token'))) {
            $factureExistante = $em->getRepository(Facture::class)->findOneBy([
                'commande' => $commande
            ]);

            if ($factureExistante) {
                $this->addFlash('danger', 'Une facture existe déjà pour cette commande.');
                return $this->redirectToRoute('gestionnaire_commande_show', ['id' => $commande->getId()]);
            }

            $facture = new Facture();
            $facture->setNumeroFacture('FAC-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6)));
            $facture->setCommande($commande);
            $facture->setDateFacturation(new \DateTimeImmutable());
            $facture->setAdresseFacturation($commande->getAdresseLivraison());

            $em->persist($facture);
            $em->flush();

            $this->addFlash('success', 'Facture ' . $facture->getNumeroFacture() . ' générée !');
        }
        return $this->redirectToRoute('gestionnaire_commande_show', ['id' => $commande->getId()]);
    }

    // ── Afficher / Télécharger le PDF de la facture ──────────────
    #[Route('/commandes/{id}/facture/pdf', name: 'gestionnaire_facture_pdf', methods: ['GET'])]
    public function afficherFacturePdf(Commande $commande, EntityManagerInterface $em): Response
    {
        $facture = $em->getRepository(Facture::class)->findOneBy([
            'commande' => $commande
        ]);

        if (!$facture) {
            $this->addFlash('danger', 'Veuillez d\'abord générer la facture.');
            return $this->redirectToRoute('gestionnaire_commande_show', ['id' => $commande->getId()]);
        }

        $logoPath = $this->getParameter('kernel.project_dir') . '/public/images/logo.png';
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, \PATHINFO_EXTENSION);
            $data = file_get_contents($logoPath);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        $html = $this->renderView('gestionnaire/facture/pdf.html.twig', [
            'commande' => $commande,
            'facture'  => $facture,
            'logo'     => $logoBase64,
        ]);

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($pdfOptions);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $facture->getNumeroFacture() . '.pdf"'
        ]);
    }

    // ── Gestion du stock ─────────────────────────────────────────
    #[Route('/stock', name: 'gestionnaire_stock_index')]
    public function stockIndex(ProduitRepository $repo): Response
    {
        return $this->render('gestionnaire/stock/index.html.twig', [
            'produits_disponibles' => $repo->findBy(['etatStock' => 'disponible']),
            'produits_rupture'     => $repo->findBy(['etatStock' => 'rupture']),
        ]);
    }

    // ── Mettre à jour le stock ───────────────────────────────────
    #[Route('/stock/{id}/update', name: 'gestionnaire_stock_update', methods: ['POST'])]
    public function stockUpdate(
        Request $request,
        \App\Entity\Produit $produit,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('stock' . $produit->getId(), $request->getPayload()->getString('_token'))) {
            $produit->setQuantiteStock((int) $request->request->get('quantite'));
            $produit->setEtatStock($request->request->get('etat_stock'));
            $em->flush();
            $this->addFlash('success', 'Stock mis à jour pour ' . $produit->getNom());
        }
        return $this->redirectToRoute('gestionnaire_stock_index');
    }

    // ══════════════════════════════════════════════════════════════
    // GESTION DES ÉTIQUETTES
    // ══════════════════════════════════════════════════════════════

    #[Route('/etiquettes', name: 'gestionnaire_etiquette_index')]
    public function etiquetteIndex(EtiquetteRepository $repo): Response
    {
        return $this->render('gestionnaire/etiquette/index.html.twig', [
            'etiquettes' => $repo->findAll(),
        ]);
    }

    #[Route('/etiquettes/nouveau', name: 'gestionnaire_etiquette_new', methods: ['GET', 'POST'])]
    public function etiquetteNew(Request $request, EntityManagerInterface $em): Response
    {
        $etiquette = new Etiquette();
        $form      = $this->createForm(EtiquetteType::class, $etiquette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($etiquette);
            $em->flush();
            $this->addFlash('success', 'Étiquette créée avec succès !');
            return $this->redirectToRoute('gestionnaire_etiquette_index');
        }

        return $this->render('gestionnaire/etiquette/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/etiquettes/{id}/modifier', name: 'gestionnaire_etiquette_edit', methods: ['GET', 'POST'])]
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
            return $this->redirectToRoute('gestionnaire_etiquette_index');
        }

        return $this->render('gestionnaire/etiquette/edit.html.twig', [
            'etiquette' => $etiquette,
            'form'      => $form,
        ]);
    }

    #[Route('/etiquettes/{id}/supprimer', name: 'gestionnaire_etiquette_delete', methods: ['POST'])]
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
        return $this->redirectToRoute('gestionnaire_etiquette_index');
    }

    // ── EXPÉDITION DES COMMANDES ─────────────────────────────────
    #[Route('/commandes/{id}/expedition', name: 'gestionnaire_commande_expedition', methods: ['POST'])]
    public function expedierCommande(
        Request $request,
        Commande $commande,
        EntityManagerInterface $em
    ): Response {
        if (!$commande->isPaiementValide()) {
            $this->addFlash('danger', 'Impossible d\'expédier une commande non payée.');
            return $this->redirectToRoute('gestionnaire_commande_show', ['id' => $commande->getId()]);
        }

        if ($this->isCsrfTokenValid('expedition' . $commande->getId(), $request->getPayload()->getString('_token'))) {
            $statut = $request->request->get('statut_expedition');
            $commande->setStatutExpedition($statut);
            $em->flush();

            $messages = [
                'en_preparation' => 'Commande mise en préparation (picking).',
                'expedie'        => 'Commande expédiée avec succès !',
                'livre'          => 'Commande marquée comme livrée !',
            ];

            $this->addFlash('success', $messages[$statut] ?? 'Statut mis à jour.');
        }

        return $this->redirectToRoute('gestionnaire_commande_show', [
            'id' => $commande->getId(),
        ]);
    }
}

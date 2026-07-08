<?php
// src/Controller/CommandeController.php
namespace App\Controller;

use App\Entity\Commande;
use App\Entity\DetailCommande;
use App\Repository\ProduitRepository;
use App\Repository\CommandeRepository;
use App\Repository\AdresseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

#[Route('/commande')]
#[IsGranted('ROLE_CLIENT')]
class CommandeController extends AbstractController
{
    // Ajouter un produit au panier
    #[Route('/panier/ajouter/{id}', name: 'panier_ajouter')]
    public function ajouterAuPanier(
        int $id,
        ProduitRepository $produitRepository,
        SessionInterface $session
    ): Response {
        $produit = $produitRepository->find($id);

        if (!$produit) {
            $this->addFlash('danger', 'Produit introuvable.');
            return $this->redirectToRoute('produit_index');
        }

        if ($produit->getEtatStock() !== 'disponible') {
            $this->addFlash('danger', 'Ce produit n\'est pas disponible.');
            return $this->redirectToRoute('produit_index');
        }

        $panier = $session->get('panier', []);

        if (isset($panier[$id])) {
            if (is_array($panier[$id])) {
                $panier[$id]['quantite']++;
            } else {
                $panier[$id]++;
            }
        } else {
            $panier[$id] = ['produit_id' => $id, 'quantite' => 1];
        }

        $session->set('panier', $panier);
        $this->addFlash('success', 'Produit ajouté au panier !');

        return $this->redirectToRoute('panier_voir');
    }

    // Afficher le panier
    #[Route('/panier', name: 'panier_voir')]
    public function voirPanier(
        SessionInterface $session,
        ProduitRepository $produitRepository
    ): Response {
        $panier = $session->get('panier', []);
        $panierDetails = [];
        $totalHt  = 0;
        $totalTtc = 0;

        foreach ($panier as $id => $item) {
            if (is_int($item)) {
                $produitId = $id;
                $quantite  = $item;
            } else {
                $produitId = $item['produit_id'] ?? $id;
                $quantite  = $item['quantite'] ?? 1;
            }

            $produit = $produitRepository->find($produitId);
            if ($produit) {
                $sousTotalHt  = (float)$produit->getPrixHt()  * $quantite;
                $sousTotalTtc = (float)$produit->getPrixTtc() * $quantite;
                $panierDetails[] = [
                    'produit'        => $produit,
                    'quantite'       => $quantite,
                    'sous_total_ht'  => $sousTotalHt,
                    'sous_total_ttc' => $sousTotalTtc,
                ];
                $totalHt  += $sousTotalHt;
                $totalTtc += $sousTotalTtc;
            }
        }

        return $this->render('commande/panier.html.twig', [
            'panier'    => $panierDetails,
            'total_ht'  => round($totalHt, 2),
            'total_ttc' => round($totalTtc, 2),
        ]);
    }

    // Supprimer un produit du panier
    #[Route('/panier/supprimer/{id}', name: 'panier_supprimer')]
    public function supprimerDuPanier(
        int $id,
        SessionInterface $session
    ): Response {
        $panier = $session->get('panier', []);

        if (isset($panier[$id])) {
            unset($panier[$id]);
            $session->set('panier', $panier);
            $this->addFlash('success', 'Produit retiré du panier.');
        }

        return $this->redirectToRoute('panier_voir');
    }

    // Étape intermédiaire : Formulaire de coordonnées et choix de livraison
    #[Route('/checkout', name: 'commande_checkout', methods: ['GET'])]
    public function checkout(
        SessionInterface $session,
        ProduitRepository $produitRepository
    ): Response {
        $panier = $session->get('panier', []);

        if (empty($panier)) {
            $this->addFlash('danger', 'Votre panier est vide.');
            return $this->redirectToRoute('panier_voir');
        }

        $user = $this->getUser();
        $adresses = [];
        if (method_exists($user, 'getAdresses')) {
            $adresses = $user->getAdresses();
        }

        $totalTtc = 0;
        foreach ($panier as $id => $item) {
            if (is_int($item)) {
                $produitId = $id;
                $quantite  = $item;
            } else {
                $produitId = $item['produit_id'] ?? $id;
                $quantite  = $item['quantite'] ?? 1;
            }

            $produit = $produitRepository->find($produitId);
            if ($produit) {
                $totalTtc += (float)$produit->getPrixTtc() * $quantite;
            }
        }

        return $this->render('commande/paiement.html.twig', [
            'adresses' => $adresses,
            'total_ttc' => round($totalTtc, 2),
        ]);
    }

    // Initialisation du tunnel Stripe Checkout
    #[Route('/valider', name: 'commande_valider', methods: ['POST'])]
    public function validerCommande(
        Request $request,
        SessionInterface $session,
        ProduitRepository $produitRepository,
        EntityManagerInterface $em,
        AdresseRepository $adresseRepository
    ): Response {
        $panier = $session->get('panier', []);

        if (empty($panier)) {
            $this->addFlash('danger', 'Votre panier est vide.');
            return $this->redirectToRoute('panier_voir');
        }

        // Configuration de la clé secrète Stripe (à mettre dans ton fichier .env)
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY'] ?? 'sk_test_remplacez_avec_votre_cle_secrete');

        // 1. Créer l'entité Commande (En attente de paiement)
        $commande = new Commande();
        $commande->setUser($this->getUser());
        $commande->setNumeroCommande('CMD-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6)));
        $commande->setTypePaiement('stripe');
        $commande->setPaiementValide(false); // Reste faux tant que Stripe n'a pas confirmé
        $commande->setPanierSauvegarde(false);

        $adresseId = $request->request->get('adresse_livraison');
        if ($adresseId) {
            $adresse = $adresseRepository->find($adresseId);
            if ($adresse) {
                $commande->setAdresseLivraison($adresse);
            }
        } else {
            $user = $this->getUser();
            if (method_exists($user, 'getAdresses') && !$user->getAdresses()->isEmpty()) {
                $commande->setAdresseLivraison($user->getAdresses()->first());
            }
        }

        $totalHt  = 0;
        $totalTtc = 0;
        $lineItems = []; // Contiendra les produits au format Stripe

        foreach ($panier as $id => $item) {
            if (is_int($item)) {
                $produitId = $id;
                $quantite  = $item;
            } else {
                $produitId = $item['produit_id'] ?? $id;
                $quantite  = $item['quantite'] ?? 1;
            }

            $produit = $produitRepository->find($produitId);
            if (!$produit) continue;

            $detail = new DetailCommande();
            $detail->setProduit($produit);
            $detail->setQuantite($quantite);
            $detail->setCoutUnitaireHt($produit->getPrixHt());
            $detail->setCoutUnitaireTtc($produit->getPrixTtc());
            $detail->setTvaProduit($produit->getTvaProduit());

            $sousTotalHt  = (float)$produit->getPrixHt()  * $quantite;
            $sousTotalTtc = (float)$produit->getPrixTtc() * $quantite;

            $detail->setCoutTotalHt((string)round($sousTotalHt, 2));
            $detail->setCoutTotalTtc((string)round($sousTotalTtc, 2));

            $commande->addDetailCommande($detail);

            $totalHt  += $sousTotalHt;
            $totalTtc += $sousTotalTtc;

            // Ajout du produit pour Stripe (Calcul en centimes obligatoire !)
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $produit->getNom(),
                    ],
                    'unit_amount' => (int)round($produit->getPrixTtc() * 100),
                ],
                'quantity' => $quantite,
            ];
        }

        $commande->setCoutTotalHt((string)round($totalHt, 2));
        $commande->setCoutTotalTtc((string)round($totalTtc, 2));

        $em->persist($commande);
        $em->flush();

        // Génération des URLs de retour absolues pour Stripe
        $successUrl = $this->generateUrl('commande_stripe_success', ['id' => $commande->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $cancelUrl = $this->generateUrl('panier_voir', [], UrlGeneratorInterface::ABSOLUTE_URL);

        // 2. Création de la session Stripe Checkout
        $checkoutSession = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);

        // Redirection instantanée vers l'interface hébergée de Stripe
        return $this->redirect($checkoutSession->url, 303);
    }

    // 🆕 Route de validation après succès Stripe
    #[Route('/stripe-success/{id}', name: 'commande_stripe_success', methods: ['GET'])]
    public function stripeSuccess(
        Commande $commande,
        EntityManagerInterface $em,
        SessionInterface $session
    ): Response {
        // Protection d'accès de sécurité
        if ($commande->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Paiement Stripe confirmé : on valide et on vide la session
        $commande->setPaiementValide(true);
        $em->flush();

        $session->remove('panier');

        $this->addFlash('success', 'Votre paiement a été validé avec succès via Stripe !');

        return $this->redirectToRoute('commande_confirmation', [
            'id' => $commande->getId()
        ]);
    }

    // Page de confirmation
    #[Route('/{id}/confirmation', name: 'commande_confirmation')]
    public function confirmation(Commande $commande): Response
    {
        if ($commande->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('commande/confirmation.html.twig', [
            'commande' => $commande,
        ]);
    }

    // Historique des commandes
    #[Route('/historique', name: 'commande_historique')]
    public function historique(CommandeRepository $commandeRepository): Response
    {
        $commandes = $commandeRepository->findBy(
            ['user' => $this->getUser(), 'panierSauvegarde' => false],
            ['dateCommande' => 'DESC']
        );

        return $this->render('commande/historique.html.twig', [
            'commandes' => $commandes,
        ]);
    }
}

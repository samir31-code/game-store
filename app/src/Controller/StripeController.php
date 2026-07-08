<?php
namespace App\Controller;

use App\Entity\Commande;
use App\Entity\DetailCommande;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/stripe')]
#[IsGranted('ROLE_CLIENT')]
class StripeController extends AbstractController
{
    public function __construct(
        private string $stripeSecretKey,
        private string $stripePublicKey
    ) {}

    // ── Créer une session de paiement Stripe ─────────────────
    #[Route('/checkout', name: 'stripe_checkout', methods: ['POST'])]
    public function checkout(
        Request $request,
        SessionInterface $session,
        ProduitRepository $produitRepository
    ): Response {
        \Stripe\Stripe::setApiKey($this->stripeSecretKey);

        $panier = $session->get('panier', []);

        if (empty($panier)) {
            $this->addFlash('danger', 'Votre panier est vide.');
            return $this->redirectToRoute('panier_voir');
        }

        // Construire les line_items pour Stripe
        $lineItems = [];
        foreach ($panier as $item) {
            $produit = $produitRepository->find($item['produit_id']);
            if (!$produit) continue;

            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'eur',
                    'unit_amount'  => (int) round((float)$produit->getPrixTtc() * 100), // en centimes
                    'product_data' => [
                        'name'        => $produit->getNom(),
                        'description' => $produit->getMarque() ?? '',
                    ],
                ],
                'quantity' => $item['quantite'],
            ];
        }

        // Sauvegarder le mode de paiement en session
        $session->set('type_paiement', $request->request->get('type_paiement', 'carte'));

        // Créer la session Stripe
        $checkoutSession = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'success_url'          => $this->generateUrl('stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'           => $this->generateUrl('stripe_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'locale'               => 'fr',
        ]);

        // Rediriger vers Stripe
        return $this->redirect($checkoutSession->url);
    }

    // ── Paiement réussi ──────────────────────────────────────
    #[Route('/success', name: 'stripe_success')]
    public function success(
        Request $request,
        SessionInterface $session,
        ProduitRepository $produitRepository,
        EntityManagerInterface $em
    ): Response {
        \Stripe\Stripe::setApiKey($this->stripeSecretKey);

        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            return $this->redirectToRoute('app_home');
        }

        // Vérifier le paiement auprès de Stripe
        $checkoutSession = \Stripe\Checkout\Session::retrieve($sessionId);

        if ($checkoutSession->payment_status !== 'paid') {
            $this->addFlash('danger', 'Le paiement n\'a pas été confirmé.');
            return $this->redirectToRoute('panier_voir');
        }

        // Créer la commande en BDD
        $panier = $session->get('panier', []);

        if (empty($panier)) {
            return $this->redirectToRoute('app_home');
        }

        $commande = new Commande();
        $commande->setUser($this->getUser());
        $commande->setNumeroCommande('CMD-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6)));
        $commande->setTypePaiement('stripe');
        $commande->setPaiementValide(true); // ← Paiement validé automatiquement par Stripe
        $commande->setPanierSauvegarde(false);

        $totalHt  = 0;
        $totalTtc = 0;

        foreach ($panier as $item) {
            $produit = $produitRepository->find($item['produit_id']);
            if (!$produit) continue;

            $detail = new DetailCommande();
            $detail->setProduit($produit);
            $detail->setQuantite($item['quantite']);
            $detail->setCoutUnitaireHt($produit->getPrixHt());
            $detail->setCoutUnitaireTtc($produit->getPrixTtc());
            $detail->setTvaProduit($produit->getTvaProduit());

            $sHt  = (float)$produit->getPrixHt()  * $item['quantite'];
            $sTtc = (float)$produit->getPrixTtc() * $item['quantite'];

            $detail->setCoutTotalHt((string)round($sHt, 2));
            $detail->setCoutTotalTtc((string)round($sTtc, 2));

            $commande->addDetailCommande($detail);

            $totalHt  += $sHt;
            $totalTtc += $sTtc;
        }

        $commande->setCoutTotalHt((string)round($totalHt, 2));
        $commande->setCoutTotalTtc((string)round($totalTtc, 2));

        $em->persist($commande);
        $em->flush();

        // Vider le panier
        $session->remove('panier');

        $this->addFlash('success', '✅ Paiement confirmé ! Commande N° : ' . $commande->getNumeroCommande());

        return $this->redirectToRoute('commande_confirmation', [
            'id' => $commande->getId(),
        ]);
    }

    // ── Paiement annulé ──────────────────────────────────────
    #[Route('/cancel', name: 'stripe_cancel')]
    public function cancel(): Response
    {
        $this->addFlash('danger', 'Paiement annulé. Votre panier est conservé.');
        return $this->redirectToRoute('panier_voir');
    }
}

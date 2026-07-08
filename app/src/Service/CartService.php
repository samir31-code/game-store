<?php

namespace App\Service;

use App\Entity\Produit;
use App\Entity\Commande;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    public function __construct(
        private RequestStack $requestStack,
        private EntityManagerInterface $em,
        private Security $security,
        private CommandeRepository $commandeRepo
    ) {}

    private function getSession()
    {
        return $this->requestStack->getSession();
    }

    // 1. Ajouter un produit au panier (+1)
    public function add(int $id): void
    {
        $session = $this->getSession();
        $panier = $session->get('panier', []);

        if (!empty($panier[$id])) {
            $panier[$id]++;
        } else {
            $panier[$id] = 1;
        }

        $session->set('panier', $panier);
        $this->sauvegarderPanierEnBDD($panier);
    }

    // 2. Diminuer la quantité d'un produit (-1)
    public function decrease(int $id): void
    {
        $session = $this->getSession();
        $panier = $session->get('panier', []);

        if (!empty($panier[$id])) {
            if ($panier[$id] > 1) {
                $panier[$id]--;
            } else {
                unset($panier[$id]);
            }
        }

        $session->set('panier', $panier);
        $this->sauvegarderPanierEnBDD($panier);
    }

    // 3. Supprimer complètement un produit du panier
    public function remove(int $id): void
    {
        $session = $this->getSession();
        $panier = $session->get('panier', []);

        if (isset($panier[$id])) {
            unset($panier[$id]);
        }

        $session->set('panier', $panier);
        $this->sauvegarderPanierEnBDD($panier);
    }

    // 4. Calculer le nombre total d'articles (pour la bulle/badge de la navbar)
    public function getTotalQuantity(): int
    {
        $panier = $this->getSession()->get('panier', []);
        $total = 0;

        foreach ($panier as $quantite) {
            $total += $quantite;
        }

        return $total;
    }

    // 5. Récupérer le panier détaillé avec les vrais objets "Produit"
    public function getDetailedCart(): array
    {
        $panier = $this->getSession()->get('panier', []);
        $panierDetaille = [];

        foreach ($panier as $id => $quantite) {
            $produit = $this->em->getRepository(Produit::class)->find($id);
            if ($produit) {
                $panierDetaille[] = [
                    'produit' => $produit,
                    'quantite' => $quantite
                ];
            }
        }

        return $panierDetaille;
    }

    // 6. Calculer le prix total global TTC du panier
    public function getTotalPrice(): float
    {
        $total = 0;
        foreach ($this->getDetailedCart() as $item) {
            $total += $item['produit']->getPrixTtc() * $item['quantite'];
        }

        return $total;
    }

    // 7. Sauvegarde automatique en Base de Données si l'utilisateur est connecté
    private function sauvegarderPanierEnBDD(array $panier): void
    {
        $user = $this->security->getUser();
        if (!$user) {
            return;
        }

        $commandePanier = $this->commandeRepo->findOneBy([
            'user' => $user,
            'panierSauvegarde' => true
        ]);

        if (!$commandePanier) {
            $commandePanier = new Commande();
            $commandePanier->setUser($user);
            $commandePanier->setPanierSauvegarde(true);
            $commandePanier->setPaiementValide(false);
            $commandePanier->setDateCommande(new \DateTimeImmutable());
            $commandePanier->setNumeroCommande(uniqid('CMD-'));
            $commandePanier->setTypePaiement('en_attente');
        }

        $totalHt = 0;
        $totalTtc = 0;

        foreach ($panier as $id => $quantite) {
            $produit = $this->em->getRepository(Produit::class)->find($id);
            if ($produit) {
                // On accumule les prix selon la quantité
                $totalHt += $produit->getPrixHt() * $quantite;
                $totalTtc += $produit->getPrixTtc() * $quantite;
            }
        }

        $commandePanier->setPanier($panier);
        $commandePanier->setCoutTotalHt($totalHt);
        $commandePanier->setCoutTotalTtc($totalTtc);

        $this->em->persist($commandePanier);
        $this->em->flush();
    }
}

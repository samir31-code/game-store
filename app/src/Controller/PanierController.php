<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PanierController extends AbstractController
{
    // Afficher la liste réelle des produits du panier
    #[Route('/panier', name: 'panier_voir')]
    public function voir(CartService $cartService): Response
    {
        return $this->render('panier/index.html.twig', [
            'items' => $cartService->getDetailedCart(),
            'total' => $cartService->getTotalPrice()
        ]);
    }

    // Intercepter l'ajout (+1)
    #[Route('/panier/ajouter/{id}', name: 'panier_ajouter')]
    public function ajouter(int $id, CartService $cartService, Request $request): Response
    {
        $cartService->add($id);

        // Redirige vers la page d'où l'on vient (catalogue ou panier directement)
        $referer = $request->headers->get('referer');
        return $referer ? $this->redirect($referer) : $this->redirectToRoute('produit_index');
    }

    // Intercepter la diminution (-1)
    #[Route('/panier/diminuer/{id}', name: 'panier_diminuer')]
    public function diminuer(int $id, CartService $cartService): Response
    {
        $cartService->decrease($id);
        return $this->redirectToRoute('panier_voir');
    }

    // Intercepter la suppression totale d'une ligne
    #[Route('/panier/supprimer/{id}', name: 'panier_supprimer')]
    public function supprimer(int $id, CartService $cartService): Response
    {
        $cartService->remove($id);
        $this->addFlash('info', 'Article retiré du panier.');
        return $this->redirectToRoute('panier_voir');
    }
}

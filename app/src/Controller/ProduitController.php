<?php
namespace App\Controller;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use App\Repository\CategorieProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/produits')]
class ProduitController extends AbstractController
{
    #[Route('/', name: 'produit_index')]
    public function index(
        Request $request,
        ProduitRepository $produitRepository,
        CategorieProduitRepository $categorieRepository
    ): Response {
        $categorieId = $request->query->get('categorie');
        $recherche   = $request->query->get('q');

        // Recherche par mot-clé
        if ($recherche) {
            $produits = $produitRepository->rechercher($recherche);
        } elseif ($categorieId) {
            $produits = $produitRepository->findBy(['categorie' => $categorieId]);
        } else {
            $produits = $produitRepository->findAll();
        }

        return $this->render('produit/index.html.twig', [
            'produits'              => $produits,
            'categories'            => $categorieRepository->findAll(),
            'categorieSelectionnee' => $categorieId,
            'recherche'             => $recherche,
        ]);
    }

    #[Route('/{id}', name: 'produit_show', requirements: ['id' => '\d+'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }
}

<?php
// src/Controller/HomeController.php
namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProduitRepository $produitRepository): Response
    {
        // Récupérer les 8 derniers produits disponibles
        $produits = $produitRepository->findBy(
            ['etatStock' => 'disponible'],
            ['dateCreation' => 'DESC'],
            8
        );

        return $this->render('home/index.html.twig', [
            'produits' => $produits,
        ]);
    }
}

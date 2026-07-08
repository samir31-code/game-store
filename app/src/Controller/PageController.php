<?php
// src/Controller/PageController.php
namespace App\Controller;

use App\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageController extends AbstractController
{
    // Afficher une page par son slug
    #[Route('/page/{slug}', name: 'page_show')]
    public function show(string $slug, PageRepository $pageRepository): Response
    {
        $page = $pageRepository->findOneBy([
            'slug'       => $slug,
            'estPubliee' => true   // Afficher uniquement les pages publiées
        ]);

        if (!$page) {
            throw $this->createNotFoundException('Page introuvable.');
        }

        return $this->render('page/show.html.twig', [
            'page' => $page,
        ]);
    }
}

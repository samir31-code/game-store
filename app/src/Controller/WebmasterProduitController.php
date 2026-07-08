<?php
namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/webmaster/produit')]
#[IsGranted('ROLE_WEBMASTER')]  // Webmaster et supérieur
class WebmasterProduitController extends AbstractController
{
    // Liste des produits — lecture seule pour webmaster
    #[Route('/', name: 'webmaster_produit_index')]
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('webmaster/produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    // Modifier un produit — webmaster peut modifier
    #[Route('/{id}/modifier', name: 'webmaster_produit_edit', methods: ['GET', 'POST'])]
    public function edit(
        Produit $produit,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $prixHt = (float) $produit->getPrixHt();
            $tva    = (float) $produit->getTvaProduit();
            $produit->setPrixTtc((string) round($prixHt * (1 + $tva / 100), 2));
            $em->flush();
            $this->addFlash('success', 'Produit modifié avec succès !');
            return $this->redirectToRoute('webmaster_produit_index');
        }

        return $this->render('webmaster/produit/edit.html.twig', [
            'produit' => $produit,
            'form'    => $form,
        ]);
    }
}

<?php
namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/produit')]
#[IsGranted('ROLE_ADMIN_PRODUIT')]  // Admin produit ET admin site
class AdminProduitController extends AbstractController
{
    // Liste des produits — Admin produit et supérieur
    #[Route('/', name: 'admin_produit_index')]
    public function index(
        Request $request,
        ProduitRepository $repo
    ): Response {
        $recherche = $request->query->get('q');

        $produits = $recherche
            ? $repo->rechercher($recherche)
            : $repo->findAll();

        return $this->render('admin/produit/index.html.twig', [
            'produits'  => $produits,
            'recherche' => $recherche,
        ]);
    }

    // Créer un produit — Admin produit et supérieur
    #[Route('/nouveau', name: 'admin_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $prixHt = (float) $produit->getPrixHt();
            $tva    = (float) $produit->getTvaProduit();
            $produit->setPrixTtc((string) round($prixHt * (1 + $tva / 100), 2));
            $em->persist($produit);
            $em->flush();
            $this->addFlash('success', 'Produit créé avec succès !');
            return $this->redirectToRoute('admin_produit_index');
        }

        return $this->render('admin/produit/new.html.twig', ['form' => $form]);
    }

    // Modifier un produit — Webmaster ET admin produit ET admin site
    #[Route('/{id}/modifier', name: 'admin_produit_edit', methods: ['GET', 'POST'])]
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
            return $this->redirectToRoute('admin_produit_index');
        }

        return $this->render('admin/produit/edit.html.twig', [
            'produit' => $produit,
            'form'    => $form,
        ]);
    }

    // Supprimer un produit — Admin produit et supérieur
    #[Route('/{id}/supprimer', name: 'admin_produit_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Produit $produit,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($produit);
            $em->flush();
            $this->addFlash('success', 'Produit supprimé.');
        }
        return $this->redirectToRoute('admin_produit_index');
    }
}

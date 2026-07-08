<?php
namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_index')]
    public function index(
        UserRepository $userRepository,
        CommandeRepository $commandeRepository,
        ProduitRepository $produitRepository
    ): Response {
        $tousLesUsers = $userRepository->findAll();

        // Grouper les utilisateurs par rôle
        $clients       = [];
        $webmasters    = [];
        $gestionnaires = [];
        $adminProduits = [];
        $admins        = [];

        foreach ($tousLesUsers as $user) {
            $roles = $user->getRoles();
            if (in_array('ROLE_ADMIN', $roles)) {
                $admins[] = $user;
            } elseif (in_array('ROLE_ADMIN_PRODUIT', $roles)) {
                $adminProduits[] = $user;
            } elseif (in_array('ROLE_GESTIONNAIRE_COMMANDE', $roles)) {
                $gestionnaires[] = $user;
            } elseif (in_array('ROLE_WEBMASTER', $roles)) {
                $webmasters[] = $user;
            } else {
                $clients[] = $user;
            }
        }

        return $this->render('admin/index.html.twig', [
            'nb_users'      => count($tousLesUsers),
            'nb_commandes'  => count($commandeRepository->findAll()),
            'nb_produits'   => count($produitRepository->findAll()),
            'clients'       => $clients,
            'webmasters'    => $webmasters,
            'gestionnaires' => $gestionnaires,
            'adminProduits' => $adminProduits,
            'admins'        => $admins,
        ]);
    }
}

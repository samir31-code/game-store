<?php
namespace App\EventListener;

use App\Entity\Commande;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class LoginListener
{
    public function __construct(private EntityManagerInterface $em) {}

    #[AsEventListener(event: LoginSuccessEvent::class)]
    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        $request = $event->getRequest();
        $session = $request->getSession();

        // 1. On cherche s'il y a un panier au nom de ce client en BDD
        $panierSauvegarde = $this->em->getRepository(Commande::class)->findOneBy([
            'user' => $user,
            'panierSauvegarde' => true
        ]);

        if ($panierSauvegarde) {
            // 2. On extrait les données (on récupère le tableau d'IDs et quantités)
            $donneesPanier = $panierSauvegarde->getContenuJson();

            if (!empty($donneesPanier)) {
                // 3. On écrase (ou on fusionne) la session avec le panier retrouvé
                $session->set('panier', $donneesPanier);
            }
        }
    }
}

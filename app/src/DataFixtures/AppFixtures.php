<?php
// src/DataFixtures/AppFixtures.php
namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Produit;
use App\Entity\Etiquette;
use App\Entity\CategorieProduit;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // ── Créer les catégories ──────────────────────────────────
        $categories = [];
        foreach ([
            'Jeux PS5',
            'Jeux Xbox',
            'Jeux PC',
            'Consoles',
            'Accessoires'
        ] as $nomCat) {
            $cat = new CategorieProduit();
            $cat->setNom($nomCat);
            $cat->setDescription('Catégorie : ' . $nomCat);
            $manager->persist($cat);
            $categories[$nomCat] = $cat;
        }

        // ── Créer les étiquettes ──────────────────────────────────
        $etiquettes = [];
        foreach ([
            'Nouveauté',
            'Bestseller',
            'Promo',
            'Exclusivité',
            'Collector'
        ] as $nomEtiq) {
            $etiq = new Etiquette();
            $etiq->setNom($nomEtiq);
            $etiq->setDescription('Étiquette : ' . $nomEtiq);
            $manager->persist($etiq);
            $etiquettes[$nomEtiq] = $etiq;
        }

        // ── Créer les produits ────────────────────────────────────
        // Format : [référence, nom, marque, prixHt, tva, catégorie, étiquettes]
        $produitsData = [
            ['REF-001', 'Spider-Man 2',      'Sony',       59.99, 20, 'Jeux PS5',    ['Nouveauté', 'Bestseller']],
            ['REF-002', 'Halo Infinite',     'Microsoft',  49.99, 20, 'Jeux Xbox',   ['Bestseller']],
            ['REF-003', 'Cyberpunk 2077',    'CD Projekt', 39.99, 20, 'Jeux PC',     ['Promo']],
            ['REF-004', 'Elden Ring',        'Bandai',     54.99, 20, 'Jeux PS5',    ['Bestseller']],
            ['REF-005', 'Zelda TotK',        'Nintendo',   59.99, 20, 'Jeux PC',     ['Nouveauté', 'Bestseller']],
            ['REF-006', 'PlayStation 5',     'Sony',      499.99, 20, 'Consoles',    ['Exclusivité']],
            ['REF-007', 'Xbox Series X',     'Microsoft', 499.99, 20, 'Consoles',    ['Exclusivité']],
            ['REF-008', 'Manette DualSense', 'Sony',       69.99, 20, 'Accessoires', ['Nouveauté']],
            ['REF-009', 'Casque Pulse 3D',   'Sony',       89.99, 20, 'Accessoires', ['Promo']],
            ['REF-010', 'FIFA 24',           'EA Sports',  59.99, 20, 'Jeux PS5',    ['Promo', 'Bestseller']],
        ];

        foreach ($produitsData as [$ref, $nom, $marque, $prixHt, $tva, $catNom, $etiqNoms]) {
            $produit = new Produit();
            $produit->setReferenceProduit($ref);
            $produit->setNom($nom);
            $produit->setMarque($marque);
            $produit->setDescription('Description de ' . $nom);
            $produit->setPrixHt((string)$prixHt);
            $produit->setTvaProduit((string)$tva);

            // Calcul automatique du prix TTC
            $prixTtc = round($prixHt * (1 + $tva / 100), 2);
            $produit->setPrixTtc((string)$prixTtc);

            $produit->setQuantiteStock(rand(5, 50));
            $produit->setEtatStock('disponible');
            $produit->setCategorie($categories[$catNom]);
            $produit->setDateCreation(new \DateTimeImmutable());
            $produit->setDateModification(null);

            // Ajouter les étiquettes
            foreach ($etiqNoms as $etiqNom) {
                $produit->addEtiquette($etiquettes[$etiqNom]);
            }

            $manager->persist($produit);
        }

        // ── Créer les utilisateurs ────────────────────────────────
        // Format : [nom, prenom, email, roles]
        $usersData = [
            // Client — compte + adresses + achats
            ['Dupont',   'Jean',    'client@test.fr',        ['ROLE_CLIENT']],


            // Webmaster — modif produits + contenu + communication
            ['Bernard',  'Paul',    'webmaster@test.fr',     ['ROLE_WEBMASTER']],


            // Gestionnaire commande — commandes + stock + livraison
            ['Durand',   'Sophie',  'gestionnaire@test.fr',  ['ROLE_GESTIONNAIRE_COMMANDE']],


            // Admin produit — CRUD complet produits
            ['Leroy',    'Marc',    'adminproduit@test.fr',  ['ROLE_ADMIN_PRODUIT']],


            // Admin — gère tout
            ['Admin',    'Site',    'admin@test.fr',         ['ROLE_ADMIN']],
        ];



        foreach ($usersData as [$nom, $prenom, $email, $roles]) {
            $user = new User();
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setEmail($email);
            $user->setRoles($roles);
            // Mot de passe : 'password123' hashé automatiquement
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, 'password123')
            );
            $manager->persist($user);
        }

        $manager->flush();
    }
}

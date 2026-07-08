<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/user')]
#[IsGranted('ROLE_ADMIN')]
class AdminUserController extends AbstractController
{
    #[Route('/nouveau', name: 'admin_user_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // Récupérer le rôle depuis l'URL (?role=ROLE_WEBMASTER)
        $role  = $request->query->get('role', 'ROLE_CLIENT');
        $user  = new User();
        $form  = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            $user->setRoles([$role]);
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Compte créé avec succès !');
            return $this->redirectToRoute('admin_index');
        }

        // Label selon le rôle
        $labels = [
            'ROLE_WEBMASTER'             => 'Webmaster',
            'ROLE_GESTIONNAIRE_COMMANDE' => 'Gestionnaire',
            'ROLE_ADMIN_PRODUIT'         => 'Admin Produit',
            'ROLE_ADMIN'                 => 'Admin',
        ];

        return $this->render('admin/user/new.html.twig', [
            'form'  => $form,
            'role'  => $role,
            'label' => $labels[$role] ?? $role,
        ]);
    }

    #[Route('/{id}', name: 'admin_user_show', methods: ['GET'])]
    public function show(User $client): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'client' => $client,
        ]);
    }
}

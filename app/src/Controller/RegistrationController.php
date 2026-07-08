<?php
namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();

        // Pré-ajouter une adresse vide obligatoire
        $adresse = new Adresse();
        $adresse->setType('livraison');
        $adresse->setCodePays('FR');
        $user->getAdresses()->add($adresse);

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hasher le mot de passe
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $plainPassword)
            );

            // Rôle par défaut
            $user->setRoles(['ROLE_CLIENT']);

            // Récupérer la checkbox "utiliser comme facturation"
            $utiliserFacturation = $form->get('utiliserFacturation')->getData();

            foreach ($user->getAdresses() as $adr) {
                $adr->setUser($user);
                $entityManager->persist($adr);

                // Créer une seconde adresse de facturation si coché
                if ($utiliserFacturation) {
                    $adresseFacturation = new Adresse();
                    $adresseFacturation->setUser($user);
                    $adresseFacturation->setType('facturation');
                    $adresseFacturation->setNumero($adr->getNumero());
                    $adresseFacturation->setVoie($adr->getVoie());
                    $adresseFacturation->setComplementAdresse1($adr->getComplementAdresse1());
                    $adresseFacturation->setCodePostal($adr->getCodePostal());
                    $adresseFacturation->setVille($adr->getVille());
                    $adresseFacturation->setCodePays($adr->getCodePays());
                    $entityManager->persist($adresseFacturation);
                }
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Votre compte a été créé avec succès !');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}

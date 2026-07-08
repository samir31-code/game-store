<?php
namespace App\Form;

use App\Entity\User;
use App\Form\AdresseInscriptionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ── Vous êtes : Particulier / Professionnel ───────
            ->add('typeCompte', ChoiceType::class, [
                'label'    => false,
                'choices'  => [
                    'Particulier'   => 'particulier',
                    'Professionnel' => 'professionnel',
                ],
                'expanded' => true,
                'multiple' => false,
                'data'     => 'particulier',
            ])

            // ── Email ───────────────────────────────────────────
            ->add('email', EmailType::class, [
                'label'       => 'Votre adresse email',
                'constraints' => [new Assert\NotBlank(), new Assert\Email()],
                'attr'        => ['class' => 'form-control'],
            ])

            // ── Mot de passe ────────────────────────────────────
            ->add('plainPassword', RepeatedType::class, [
                'type'           => PasswordType::class,
                'mapped'         => false,
                'first_options'  => [
                    'label' => 'Votre mot de passe',
                    'attr'  => ['class' => 'form-control'],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr'  => ['class' => 'form-control'],
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Regex([
                        'pattern' => '/^(?=.*[0-9])(?=.*[A-Z])(?=.*[\W_]).{8,}$/',
                        'message' => 'Le mot de passe doit contenir 8 caractères minimum, 1 chiffre, 1 majuscule et 1 caractère spécial.',
                    ]),
                ],
            ])

            // ── Civilité ─────────────────────────────────────────
            ->add('civilite', ChoiceType::class, [
                'label'       => 'Civilité',
                'choices'     => [
                    'Madame'   => 'madame',
                    'Monsieur' => 'monsieur',
                ],
                'placeholder' => 'Sélectionner...',
                'constraints' => [new Assert\NotBlank()],
                'attr'        => ['class' => 'form-select'],
            ])

            ->add('nom', TextType::class, [
                'label'       => 'Nom',
                'constraints' => [new Assert\NotBlank(), new Assert\Length(['min' => 2, 'max' => 100])],
                'attr'        => ['class' => 'form-control'],
            ])
            ->add('prenom', TextType::class, [
                'label'       => 'Prénom',
                'constraints' => [new Assert\NotBlank(), new Assert\Length(['min' => 2, 'max' => 100])],
                'attr'        => ['class' => 'form-control'],
            ])

            // ── Date de naissance ────────────────────────────────
            ->add('dateNaissance', DateType::class, [
                'label'    => 'Date de naissance',
                'widget'   => 'single_text',
                'required' => false,
                'attr'     => ['class' => 'form-control'],
            ])

            // ── Téléphone ─────────────────────────────────────────
            ->add('telephone', TelType::class, [
                'label'       => 'Téléphone portable',
                'constraints' => [new Assert\NotBlank(), new Assert\Length(['max' => 20])],
                'attr'        => [
                    'class'       => 'form-control',
                    'placeholder' => '06 12 34 56 78',
                ],
            ])

            // ── Adresse ───────────────────────────────────────────
            ->add('adresses', CollectionType::class, [
                'label'         => false,
                'entry_type'    => AdresseInscriptionType::class,
                'allow_add'     => false,
                'allow_delete'  => false,
                'by_reference'  => false,
                'required'      => true,
                'entry_options' => ['label' => false],
            ])

            // ── Checkbox utiliser comme facturation ──────────────
            ->add('utiliserFacturation', CheckboxType::class, [
                'label'    => 'Utiliser aussi comme adresse de facturation',
                'mapped'   => false,
                'required' => false,
                'data'     => true,
            ])

            // ── Préférences de communication ─────────────────────
            ->add('accepteOffresEmail', ChoiceType::class, [
                'label'    => 'Email',
                'choices'  => ['Oui' => true, 'Non' => false],
                'expanded' => true,
                'multiple' => false,
                'data'     => false,
            ])
            ->add('accepteOffresSms', ChoiceType::class, [
                'label'    => 'SMS',
                'choices'  => ['Oui' => true, 'Non' => false],
                'expanded' => true,
                'multiple' => false,
                'data'     => false,
            ])
            ->add('accepteOffresPartenaires', ChoiceType::class, [
                'label'    => "J'accepte de recevoir les offres personnalisées des partenaires",
                'choices'  => ['Oui' => true, 'Non' => false],
                'expanded' => true,
                'multiple' => false,
                'data'     => false,
            ])
            ->add('rgpdConsent', CheckboxType::class, [
                'label'       => "J'accepte les Conditions Générales d'Utilisation et la Politique de Confidentialité de GameStore.",
                'mapped'      => false,
                'constraints' => [
                    new Assert\IsTrue([
                        'message' => 'Vous devez accepter notre politique de confidentialité pour créer un compte.',
                    ]),
                ],
                'attr' => ['class' => 'form-check-input champ-requis']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}

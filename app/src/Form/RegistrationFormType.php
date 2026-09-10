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
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez saisir une adresse email.',
                    ]),
                    new Assert\Email([
                        'message' => 'Veuillez saisir une adresse email valide.',
                    ]),
                ],
                'attr' => ['class' => 'form-control'],
            ])

            // ── Mot de passe ────────────────────────────────────
            ->add('plainPassword', RepeatedType::class, [
                'type'            => PasswordType::class,
                'mapped'          => false,
                'first_options'   => [
                    'label' => 'Votre mot de passe',
                    'attr'  => ['class' => 'form-control'],
                ],
                'second_options'  => [
                    'label' => 'Confirmer le mot de passe',
                    'attr'  => ['class' => 'form-control'],
                ],
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'constraints'     => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez renseigner un mot de passe.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^(?=.*[0-9])(?=.*[A-Z])(?=.*[^A-Za-z0-9]).{8,}$/',
                        'message' => 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, un chiffre et un caractère spécial.',
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
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez sélectionner votre civilité.',
                    ]),
                ],
                'attr' => ['class' => 'form-select'],
            ])

            ->add('nom', TextType::class, [
                'label'       => 'Nom',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le nom est obligatoire.',
                    ]),
                    new Assert\Length([
                        'min'        => 2,
                        'max'        => 100,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
                'attr' => ['class' => 'form-control'],
            ])

            ->add('prenom', TextType::class, [
                'label'       => 'Prénom',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le prénom est obligatoire.',
                    ]),
                    new Assert\Length([
                        'min'        => 2,
                        'max'        => 100,
                        'minMessage' => 'Le prénom doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le prénom ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
                'attr' => ['class' => 'form-control'],
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
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le numéro de téléphone est obligatoire.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/',
                        'message' => 'Veuillez saisir un numéro de téléphone valide.',
                    ]),
                ],
                'attr' => [
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
                'attr' => ['class' => 'form-check-input champ-requis'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

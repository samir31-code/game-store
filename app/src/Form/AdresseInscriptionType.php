<?php
namespace App\Form;

use App\Entity\Adresse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class AdresseInscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Type caché — sera défini lors du paiement
            ->add('type', HiddenType::class, [
                'data' => 'livraison',
            ])
            ->add('numero', TextType::class, [
                'label'    => 'Numéro',
                'required' => false,
                'attr'     => [
                    'class'       => 'form-control',
                    'placeholder' => 'Ex: 12',
                ],
            ])
            ->add('voie', TextType::class, [
                'label'       => 'Voie',
                'constraints' => [
                    new Assert\NotBlank(message: 'La voie est obligatoire.'),
                    new Assert\Length(['max' => 35]),
                ],
                'attr' => [
                    'class'       => 'form-control',
                    'placeholder' => 'Ex: rue de la Paix',
                ],
            ])
            ->add('complementAdresse1', TextType::class, [
                'label'    => 'Complément d\'adresse',
                'required' => false,
                'attr'     => [
                    'class'       => 'form-control',
                    'placeholder' => 'Appartement, étage...',
                ],
            ])
            ->add('codePostal', TextType::class, [
                'label'       => 'Code postal',
                'constraints' => [
                    new Assert\NotBlank(message: 'Le code postal est obligatoire.'),
                    new Assert\Regex([
                        'pattern' => '/^\d{5}$/',
                        'message' => 'Le code postal doit contenir 5 chiffres.',
                    ]),
                ],
                'attr' => [
                    'class'       => 'form-control',
                    'placeholder' => 'Ex: 75001',
                    'maxlength'   => '5',
                ],
            ])
            ->add('ville', TextType::class, [
                'label'       => 'Ville',
                'constraints' => [
                    new Assert\NotBlank(message: 'La ville est obligatoire.'),
                ],
                'attr' => [
                    'class'       => 'form-control',
                    'placeholder' => 'Ex: Paris',
                ],
            ])
            // France uniquement
            ->add('codePays', HiddenType::class, [
                'data' => 'FR',
            ])
            ->add('detailLivraison', TextType::class, [
                'label'    => 'Instructions de livraison',
                'required' => false,
                'attr'     => [
                    'class'       => 'form-control',
                    'placeholder' => 'Code d\'accès, digicode...',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Adresse::class]);
    }
}

<?php
namespace App\Form;

use App\Entity\Adresse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class AdresseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label'   => 'Type d\'adresse',
                'choices' => [
                    'Livraison'   => 'livraison',
                    'Facturation' => 'facturation',
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('numero', TextType::class, [
                'label'    => 'Numéro',
                'required' => false,
                'attr'     => ['class' => 'form-control', 'placeholder' => 'Ex: 12'],
            ])
            ->add('voie', TextType::class, [
                'label'       => 'Voie',
                'constraints' => [new Assert\NotBlank(), new Assert\Length(['max' => 35])],
                'attr'        => ['class' => 'form-control', 'placeholder' => 'Ex: rue de la Paix'],
            ])
            ->add('complementAdresse1', TextType::class, [
                'label'    => 'Complément 1',
                'required' => false,
                'attr'     => ['class' => 'form-control', 'placeholder' => 'Appartement, étage...'],
            ])
            ->add('complementAdresse2', TextType::class, [
                'label'    => 'Complément 2',
                'required' => false,
                'attr'     => ['class' => 'form-control'],
            ])
            ->add('complementAdresse3', TextType::class, [
                'label'    => 'Complément 3',
                'required' => false,
                'attr'     => ['class' => 'form-control'],
            ])
            ->add('codePostal', TextType::class, [
                'label'       => 'Code postal',
                'constraints' => [new Assert\NotBlank()],
                'attr'        => ['class' => 'form-control', 'placeholder' => 'Ex: 75001'],
            ])
            ->add('ville', TextType::class, [
                'label'       => 'Ville',
                'constraints' => [new Assert\NotBlank()],
                'attr'        => ['class' => 'form-control', 'placeholder' => 'Ex: Paris'],
            ])
            // codePays caché — toujours FR
            ->add('codePays', HiddenType::class, [
                'data' => 'FR',
            ])
            ->add('detailLivraison', TextType::class, [
                'label'    => 'Détail livraison',
                'required' => false,
                'attr'     => ['class' => 'form-control', 'placeholder' => 'Instructions particulières...'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Adresse::class]);
    }
}

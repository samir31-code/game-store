<?php
// src/Form/ProduitType.php
namespace App\Form;

use App\Entity\Produit;
use App\Entity\Etiquette;
use App\Entity\CategorieProduit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('referenceProduit', TextType::class, [
                'label' => 'Référence',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 50])
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom du produit',
                'constraints' => [new Assert\NotBlank()],
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 4]
            ])
            ->add('marque', TextType::class, [
                'label' => 'Marque',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('prixHt', NumberType::class, [
                'label' => 'Prix HT (€)',
                'constraints' => [new Assert\NotBlank(), new Assert\Positive()],
                'attr' => ['class' => 'form-control', 'step' => '0.01']
            ])
            ->add('tvaProduit', NumberType::class, [
                'label' => 'TVA (%)',
                'constraints' => [new Assert\NotBlank()],
                'attr' => ['class' => 'form-control']
            ])
            ->add('quantiteStock', NumberType::class, [
                'label' => 'Quantité en stock',
                'constraints' => [new Assert\NotBlank()],
                'attr' => ['class' => 'form-control']
            ])
            ->add('etatStock', ChoiceType::class, [
                'label' => 'État du stock',
                'choices' => [
                    'Disponible'    => 'disponible',
                    'Rupture'       => 'rupture',
                    'Sur commande'  => 'sur_commande',
                ],
                'attr' => ['class' => 'form-select']
            ])
            // Liste déroulante liée à la table categorie_produit
            ->add('categorie', EntityType::class, [
                'label' => 'Catégorie',
                'class' => CategorieProduit::class,
                'choice_label' => 'nom',
                'attr' => ['class' => 'form-select']
            ])
            // Cases à cocher liées à la table etiquette
            ->add('etiquettes', EntityType::class, [
                'label' => 'Étiquettes',
                'class' => Etiquette::class,
                'choice_label' => 'nom',
                'multiple' => true,   // plusieurs étiquettes possibles
                'expanded' => true,   // affiche des cases à cocher
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}

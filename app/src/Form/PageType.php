<?php
// src/Form/PageType.php
namespace App\Form;

use App\Entity\Page;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de la page',
                'constraints' => [new Assert\NotBlank()],
                'attr' => ['class' => 'form-control']
            ])
            ->add('slug', TextType::class, [
                'label' => 'URL de la page (ex: a-propos)',
                'constraints' => [new Assert\NotBlank()],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'ex: a-propos, contact, mentions-legales'
                ]
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Contenu de la page',
                'constraints' => [new Assert\NotBlank()],
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 15
                ]
            ])
            ->add('estPubliee', CheckboxType::class, [
                'label'    => 'Page publiée',
                'required' => false,
                'attr'     => ['class' => 'form-check-input']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Page::class]);
    }
}

<?php
// src/Form/UserManagementType.php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserManagementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'constraints' => [new Assert\NotBlank()],
                'attr' => ['class' => 'form-control']
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [new Assert\NotBlank()],
                'attr' => ['class' => 'form-control']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [new Assert\NotBlank(), new Assert\Email()],
                'attr' => ['class' => 'form-control']
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôle',
                'choices' => [
                    'Client'        => 'ROLE_CLIENT',
                    'Webmaster'     => 'ROLE_WEBMASTER',
                    'Admin Produit' => 'ROLE_ADMIN_PRODUIT',
                    'Admin Site'    => 'ROLE_ADMIN_SITE',
                ],
                'multiple' => true,
                'expanded' => true,  // Cases à cocher
                'attr' => ['class' => 'form-check']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}

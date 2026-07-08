<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Message;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sujet', TextType::class, [
                'label'       => 'Sujet',
                'constraints' => [new Assert\NotBlank()],
                'attr'        => ['class' => 'form-control'],
            ])
            ->add('contenu', TextareaType::class, [
                'label'       => 'Message',
                'constraints' => [new Assert\NotBlank()],
                'attr'        => ['class' => 'form-control', 'rows' => 6],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Message::class]);
    }
}

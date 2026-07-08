<?php
namespace App\Form;

use App\Entity\DemandeAssistance;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ReponseAssistanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reponse', TextareaType::class, [
                'label'       => 'Votre réponse',
                'constraints' => [new Assert\NotBlank()],
                'attr'        => ['class' => 'form-control', 'rows' => 6],
            ])
            ->add('statut', ChoiceType::class, [
                'label'   => 'Statut de la demande',
                'choices' => [
                    'Ouvert'    => 'ouvert',
                    'En cours'  => 'en_cours',
                    'Résolu'    => 'resolu',
                ],
                'attr' => ['class' => 'form-select'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => DemandeAssistance::class]);
    }
}

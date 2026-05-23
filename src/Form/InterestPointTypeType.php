<?php

namespace App\Form;

use App\Entity\InterestPointType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InterestPointTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du type',
                'attr' => [
                    'placeholder' => 'Ex: Cité, Donjon, Temple...',
                    'class' => 'form-control',
                ],
            ])
            ->add('color', ColorType::class, [
                'label' => 'Couleur',
                'attr' => [
                    'class' => 'form-control form-control-color',
                ],
            ])
            ->add('icon', TextType::class, [
                'label' => 'Icône (emoji)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: 🏰',
                    'class' => 'form-control',
                    'maxlength' => 10,
                ],
                'help' => 'Utilisez un emoji comme icône',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InterestPointType::class,
        ]);
    }
}

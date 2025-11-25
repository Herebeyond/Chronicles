<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRolesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôles assignés',
                'choices' => User::getAvailableRoles(),
                'choice_label' => function ($choice, $key, $value) {
                    return $choice;
                },
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'help' => 'Sélectionnez un ou plusieurs rôles. Laissez vide pour supprimer tous les rôles.',
                'attr' => [
                    'class' => 'roles-checklist',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
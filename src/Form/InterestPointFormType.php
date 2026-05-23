<?php

namespace App\Form;

use App\Entity\InterestPoint;
use App\Entity\InterestPointType as InterestPointTypeEntity;
use App\Entity\Map;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class InterestPointFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du lieu',
                'attr' => [
                    'placeholder' => 'Ex: Citadelle des Elfes',
                    'class' => 'form-control',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Description du lieu...',
                    'class' => 'form-control',
                    'rows' => 5,
                ],
            ])
            ->add('otherNames', TextType::class, [
                'label' => 'Autres noms',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: La Forteresse Blanche | Elthorin',
                    'class' => 'form-control',
                ],
                'help' => 'Séparez les noms par le caractère |',
            ])
            ->add('map', EntityType::class, [
                'class' => Map::class,
                'label' => 'Carte',
                'choice_label' => 'name',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('type', EntityType::class, [
                'class' => InterestPointTypeEntity::class,
                'label' => 'Type de lieu',
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Sélectionner un type...',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('xCoordinate', HiddenType::class, [
                'attr' => ['class' => 'x-coordinate'],
            ])
            ->add('yCoordinate', HiddenType::class, [
                'attr' => ['class' => 'y-coordinate'],
            ])
            ->add('mainImageUpload', FileType::class, [
                'label' => 'Image principale',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide',
                    ])
                ],
                'attr' => [
                    'accept' => 'image/jpeg,image/png,image/gif,image/webp',
                    'class' => 'form-control',
                ],
                'help' => 'Formats: JPG, PNG, GIF, WebP (max 5 Mo). Les images de galerie peuvent être ajoutées depuis la page du lieu.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InterestPoint::class,
        ]);
    }
}

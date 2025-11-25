<?php

namespace App\Form;

use App\Entity\Species;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File;

class SpeciesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Species Name',
                'attr' => [
                    'placeholder' => 'Enter species name (e.g., Humans, Elves)',
                    'class' => 'form-input'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Species name is required.'
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Name must be at least {{ limit }} characters long.',
                        'maxMessage' => 'Name cannot exceed {{ limit }} characters.'
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Species description (optional)',
                    'rows' => 4,
                    'class' => 'form-input'
                ],
                'constraints' => [
                    new Length([
                        'max' => 5000,
                        'maxMessage' => 'Description cannot exceed {{ limit }} characters.'
                    ])
                ]
            ])
            ->add('iconFile', FileType::class, [
                'label' => 'Species Icon',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-input file-input',
                    'accept' => 'image/*'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file (JPEG, PNG, GIF, WebP).',
                        'maxSizeMessage' => 'Image file is too large ({{ size }} {{ suffix }}). Maximum allowed size is {{ limit }} {{ suffix }}.'
                    ])
                ]
            ])
            ->add('icon', TextType::class, [
                'label' => 'Current Icon',
                'required' => false,
                'attr' => [
                    'readonly' => true,
                    'class' => 'form-input readonly-input'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Species::class,
        ]);
    }
}

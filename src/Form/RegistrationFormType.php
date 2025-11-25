<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PasswordStrength;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                'attr' => [
                    'placeholder' => 'Choisissez un nom d\'utilisateur unique',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un nom d\'utilisateur',
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Votre nom d\'utilisateur doit faire au moins {{ limit }} caractères',
                        'max' => 100,
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'attr' => [
                    'placeholder' => 'votre@email.com',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer une adresse email',
                    ]),
                ],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Votre prénom (optionnel)',
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Votre nom de famille (optionnel)',
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'first_options' => [
                    'label' => 'Mot de passe',
                    'attr' => [
                        'placeholder' => 'Choisissez un mot de passe sécurisé',
                        'class' => 'password-field',
                        'data-password-strength' => 'true',
                    ],
                    'help' => 'Le mot de passe doit contenir au moins 8 caractères avec une majuscule, une minuscule, un chiffre et un caractère spécial.',
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => [
                        'placeholder' => 'Répétez le mot de passe',
                        'class' => 'password-field',
                    ],
                ],
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Votre mot de passe doit faire au moins {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                    
                    // Option: Use Symfony's built-in PasswordStrength (uncomment to use)
                    new PasswordStrength([
                        'minScore' => PasswordStrength::STRENGTH_STRONG,
                        'message' => 'Le mot de passe doit être plus sécurisé.',
                    ]),
                    
                    // Option: Use individual Regex constraints (uncomment to use)
                    new Regex(['pattern' => '/[A-Z]/', 'message' => 'Une lettre majuscule requise']),
                    new Regex(['pattern' => '/[a-z]/', 'message' => 'Une lettre minuscule requise']),
                    new Regex(['pattern' => '/\d/', 'message' => 'Un chiffre requis']),
                    new Regex(['pattern' => '/[!@#$%^&*()]/', 'message' => 'Un caractère spécial requis']),
                    
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'J\'accepte les conditions d\'utilisation',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions d\'utilisation.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
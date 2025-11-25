<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class StrongPassword extends Constraint
{
    public string $message = 'Le mot de passe doit contenir au moins {{ min_length }} caractères, avec au moins une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial.';
    
    public string $tooShortMessage = 'Le mot de passe doit contenir au moins {{ min_length }} caractères.';
    public string $missingUppercaseMessage = 'Le mot de passe doit contenir au moins une lettre majuscule.';
    public string $missingLowercaseMessage = 'Le mot de passe doit contenir au moins une lettre minuscule.';
    public string $missingNumberMessage = 'Le mot de passe doit contenir au moins un chiffre.';
    public string $missingSpecialMessage = 'Le mot de passe doit contenir au moins un caractère spécial (!@#$%^&*()_+-=[]{}|;:,.<>?).';
    public string $commonPasswordMessage = 'Ce mot de passe est trop commun. Veuillez choisir un mot de passe plus sécurisé.';
    public string $containsUsernameMessage = 'Le mot de passe ne doit pas contenir votre nom d\'utilisateur.';
    public string $containsEmailMessage = 'Le mot de passe ne doit pas contenir votre adresse email.';
    
    public int $minLength = 8;
    public bool $requireUppercase = true;
    public bool $requireLowercase = true;
    public bool $requireNumbers = true;
    public bool $requireSpecialChars = true;
    public bool $checkCommonPasswords = true;
    public bool $checkUserData = true;
}
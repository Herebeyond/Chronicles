# Symfony Password Validation Constraints Guide

## 🔐 Built-in Symfony Password Constraints

### 1. **PasswordStrength** (Symfony 6.2+)
The main built-in password strength validator:

```php
use Symfony\Component\Validator\Constraints\PasswordStrength;

new PasswordStrength([
    'minScore' => PasswordStrength::STRENGTH_MEDIUM,
    'message' => 'Le mot de passe est trop faible.',
])
```

**Strength Levels:**
- `PasswordStrength::STRENGTH_WEAK` - Very basic requirements
- `PasswordStrength::STRENGTH_MEDIUM` - Moderate security
- `PasswordStrength::STRENGTH_STRONG` - Good security
- `PasswordStrength::STRENGTH_VERY_STRONG` - Maximum security

### 2. **Length** Constraint
Controls password length:

```php
use Symfony\Component\Validator\Constraints\Length;

new Length([
    'min' => 8,
    'max' => 4096,
    'minMessage' => 'Le mot de passe doit faire au moins {{ limit }} caractères',
    'maxMessage' => 'Le mot de passe ne peut pas dépasser {{ limit }} caractères',
])
```

### 3. **Regex** Constraints
For specific character requirements:

```php
use Symfony\Component\Validator\Constraints\Regex;

// Uppercase letter
new Regex([
    'pattern' => '/[A-Z]/',
    'message' => 'Le mot de passe doit contenir au moins une lettre majuscule',
])

// Lowercase letter
new Regex([
    'pattern' => '/[a-z]/',
    'message' => 'Le mot de passe doit contenir au moins une lettre minuscule',
])

// Number
new Regex([
    'pattern' => '/\d/',
    'message' => 'Le mot de passe doit contenir au moins un chiffre',
])

// Special characters
new Regex([
    'pattern' => '/[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/',
    'message' => 'Le mot de passe doit contenir au moins un caractère spécial',
])

// No common patterns
new Regex([
    'pattern' => '/^(?!.*(.)\1{2,})(?!.*123)(?!.*abc)(?!.*qwe).*$/',
    'message' => 'Le mot de passe ne doit pas contenir de séquences répétées',
])
```

### 4. **NotBlank** Constraint
Ensures password is not empty:

```php
use Symfony\Component\Validator\Constraints\NotBlank;

new NotBlank([
    'message' => 'Veuillez entrer un mot de passe',
])
```

### 5. **Callback** Constraint
For custom validation logic:

```php
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

new Callback([
    'callback' => function ($value, ExecutionContextInterface $context) {
        // Custom validation logic
        if (in_array(strtolower($value), ['password', '123456', 'qwerty'])) {
            $context->buildViolation('Ce mot de passe est trop commun')
                ->addViolation();
        }
    }
])
```

## 🎯 **Recommended Approaches**

### **Option A: Symfony's Built-in PasswordStrength (Simplest)**
```php
'constraints' => [
    new NotBlank(['message' => 'Veuillez entrer un mot de passe']),
    new Length(['min' => 8, 'minMessage' => 'Au moins {{ limit }} caractères']),
    new PasswordStrength([
        'minScore' => PasswordStrength::STRENGTH_STRONG,
        'message' => 'Le mot de passe doit être plus sécurisé.',
    ]),
],
```

### **Option B: Multiple Regex Constraints (Granular)**
```php
'constraints' => [
    new NotBlank(['message' => 'Veuillez entrer un mot de passe']),
    new Length(['min' => 8, 'minMessage' => 'Au moins {{ limit }} caractères']),
    new Regex(['pattern' => '/[A-Z]/', 'message' => 'Une lettre majuscule']),
    new Regex(['pattern' => '/[a-z]/', 'message' => 'Une lettre minuscule']),
    new Regex(['pattern' => '/\d/', 'message' => 'Un chiffre']),
    new Regex(['pattern' => '/[!@#$%^&*()]/', 'message' => 'Un caractère spécial']),
],
```

### **Option C: Custom Validator (Most Comprehensive - Your Current)**
```php
'constraints' => [
    new NotBlank(['message' => 'Veuillez entrer un mot de passe']),
    new Length(['min' => 8, 'minMessage' => 'Au moins {{ limit }} caractères']),
    new StrongPassword([
        'minLength' => 8,
        'requireUppercase' => true,
        'requireLowercase' => true,
        'requireNumbers' => true,
        'requireSpecialChars' => true,
        'checkCommonPasswords' => true,
        'checkUserData' => true,
    ]),
],
```

## 🚀 **Symfony Console Commands for Validation**

You can also create validators using Symfony maker commands:

```bash
# Create a custom validator
php bin/console make:validator

# Create a constraint with validator
php bin/console make:constraint
```

## 💡 **Best Practices**

1. **Combine Multiple Constraints**: Use Length + PasswordStrength + Regex for comprehensive validation
2. **User-Friendly Messages**: Provide clear, actionable error messages in French
3. **Client-Side Validation**: Complement server-side validation with JavaScript
4. **Security vs UX**: Balance security requirements with user experience
5. **Test Thoroughly**: Test with various password combinations

## 🎯 **Your Current Setup**

Your current implementation using the custom `StrongPassword` validator is actually the most comprehensive approach, offering:
- ✅ Character type requirements
- ✅ Common password detection
- ✅ Sequential pattern detection
- ✅ User data validation
- ✅ Repeated character detection
- ✅ Custom French messages

This is more advanced than Symfony's built-in `PasswordStrength` constraint!
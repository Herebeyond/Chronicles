<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\UserProfileType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/profile', name: 'profile')]
    public function profile(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        // Update last login time
        $user->setLastLoginAt(new \DateTimeImmutable());
        $entityManager->flush();

        return $this->render('security/profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route(path: '/profile/edit', name: 'profile_edit')]
    public function editProfile(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle avatar file upload
            $avatarFile = $form->get('avatarFile')->getData();
            
            if ($avatarFile) {
                // Generate a unique filename
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename . '_' . uniqid() . '.' . $avatarFile->guessExtension();
                
                try {
                    $avatarFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/images/user_icon',
                        $newFilename
                    );
                    
                    // Delete old avatar if it exists and is not the default
                    $oldAvatar = $user->getAvatarFilename();
                    if ($oldAvatar && $oldAvatar !== 'default_user_icon.png') {
                        $oldPath = $this->getParameter('kernel.project_dir') . '/public/images/user_icon/' . $oldAvatar;
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }
                    
                    $user->setAvatarFilename($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                }
            }
            
            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

            return $this->redirectToRoute('profile');
        }

        return $this->render('security/profile_edit.html.twig', [
            'user' => $user,
            'profileForm' => $form,
        ]);
    }

    #[Route(path: '/profile/change-password', name: 'change_password')]
    public function changePassword(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if the current password is correct
            $currentPassword = $form->get('currentPassword')->getData();
            if (!$userPasswordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', 'Le mot de passe actuel est incorrect.');
            } else {
                // Update the password
                $newPassword = $form->get('plainPassword')->getData();
                $user->setPassword($userPasswordHasher->hashPassword($user, $newPassword));
                
                $entityManager->flush();

                $this->addFlash('success', 'Votre mot de passe a été modifié avec succès.');

                return $this->redirectToRoute('profile');
            }
        }

        return $this->render('security/change_password.html.twig', [
            'user' => $user,
            'changePasswordForm' => $form,
        ]);
    }
}
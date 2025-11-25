<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\AdminUserType;
use App\Form\UserRolesType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
class UserManagementController extends AbstractController
{
    #[Route('', name: 'admin_users_index')]
    public function index(UserRepository $userRepository, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $roleFilter = $request->query->get('role', '');

        if ($search) {
            $users = $userRepository->searchUsers($search);
        } elseif ($roleFilter) {
            if ($roleFilter === 'no_roles') {
                $users = $userRepository->findUsersWithoutRoles();
            } else {
                $users = $userRepository->findByRole($roleFilter);
            }
        } else {
            $users = $userRepository->findBy(['isActive' => true], ['username' => 'ASC']);
        }

        $userStats = $userRepository->getUserStats();

        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
            'userStats' => $userStats,
            'search' => $search,
            'roleFilter' => $roleFilter,
            'availableRoles' => User::getAvailableRoles(),
        ]);
    }

    #[Route('/create', name: 'admin_users_create')]
    public function create(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(AdminUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setIsActive(true);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', sprintf('L\'utilisateur "%s" a été créé avec succès.', $user->getUsername()));

            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('admin/users/create.html.twig', [
            'userForm' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_users_edit', requirements: ['id' => '\d+'])]
    public function edit(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AdminUserType::class, $user, ['edit_mode' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', sprintf('L\'utilisateur "%s" a été mis à jour avec succès.', $user->getUsername()));

            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
            'userForm' => $form,
        ]);
    }

    #[Route('/{id}/roles', name: 'admin_users_roles', requirements: ['id' => '\d+'])]
    public function manageRoles(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserRolesType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', sprintf('Les rôles de "%s" ont été mis à jour avec succès.', $user->getUsername()));

            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('admin/users/roles.html.twig', [
            'user' => $user,
            'rolesForm' => $form,
        ]);
    }

    #[Route('/{id}/toggle-status', name: 'admin_users_toggle_status', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function toggleStatus(User $user, EntityManagerInterface $entityManager): Response
    {
        // Prevent disabling super admins unless current user is also super admin
        if ($user->hasRole(User::ROLE_SUPER_ADMIN) && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier le statut d\'un super administrateur.');
            return $this->redirectToRoute('admin_users_index');
        }

        $user->setIsActive(!$user->isActive());
        $entityManager->flush();

        $status = $user->isActive() ? 'activé' : 'désactivé';
        $this->addFlash('success', sprintf('L\'utilisateur "%s" a été %s.', $user->getUsername(), $status));

        return $this->redirectToRoute('admin_users_index');
    }

    #[Route('/{id}/delete', name: 'admin_users_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function delete(User $user, EntityManagerInterface $entityManager): Response
    {
        // Prevent deletion of self
        if ($user === $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            return $this->redirectToRoute('admin_users_index');
        }

        $username = $user->getUsername();
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', sprintf('L\'utilisateur "%s" a été supprimé définitivement.', $username));

        return $this->redirectToRoute('admin_users_index');
    }

    #[Route('/stats', name: 'admin_users_stats')]
    public function stats(UserRepository $userRepository): Response
    {
        $userStats = $userRepository->getUserStats();

        return $this->render('admin/users/stats.html.twig', [
            'userStats' => $userStats,
            'availableRoles' => User::getAvailableRoles(),
        ]);
    }
}
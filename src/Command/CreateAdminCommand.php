<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Create an admin user for Chronicles',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Admin email address')
            ->addOption('username', null, InputOption::VALUE_REQUIRED, 'Admin username')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Admin password')
            ->addOption('super', null, InputOption::VALUE_NONE, 'Create as Super Admin')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Get values from options or prompt
        $email = $input->getOption('email');
        $username = $input->getOption('username');
        $password = $input->getOption('password');
        $isSuper = $input->getOption('super');

        if (!$email) {
            $email = $io->ask('Email address for admin user');
        }

        if (!$username) {
            $username = $io->ask('Username for admin user');
        }

        if (!$password) {
            $password = $io->askHidden('Password for admin user');
        }

        // Check if user already exists
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $io->error(sprintf('User with email "%s" already exists!', $email));
            return Command::FAILURE;
        }

        $existingUsername = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
        if ($existingUsername) {
            $io->error(sprintf('User with username "%s" already exists!', $username));
            return Command::FAILURE;
        }

        // Create the admin user
        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setFirstName('Admin');
        $user->setLastName('Chronicles');
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setIsActive(true);

        // Set roles
        if ($isSuper) {
            $user->setRoles([User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN, User::ROLE_USER]);
            $io->note('Creating Super Admin user');
        } else {
            $user->setRoles([User::ROLE_ADMIN, User::ROLE_USER]);
            $io->note('Creating Admin user');
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('Admin user "%s" created successfully!', $username));
        
        $io->table(
            ['Property', 'Value'],
            [
                ['Email', $user->getEmail()],
                ['Username', $user->getUsername()],
                ['Full Name', $user->getFullName()],
                ['Roles', implode(', ', $user->getRoleLabels())],
                ['Status', $user->isActive() ? 'Active' : 'Inactive'],
            ]
        );

        return Command::SUCCESS;
    }
}
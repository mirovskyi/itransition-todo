<?php

namespace App\Command;

use App\Dto\UserDto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Creating new user',
)]
class CreateUserCommand extends Command
{
    private EntityManagerInterface $entityManager;

    private UserPasswordHasherInterface $passwordHasher;

    private ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->validator = $validator;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email address')
            ->addArgument('password', InputArgument::REQUIRED, 'User password')
            ->addOption('firstname', 'f', InputOption::VALUE_OPTIONAL, 'User first name')
            ->addOption('lastname', 'l', InputOption::VALUE_OPTIONAL, 'User last name')
            ->addOption('roles', 'r', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'User last name', ['ROLE_USER'])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userDto = new UserDto();
        $userDto->email = $input->getArgument('email');
        $userDto->password = $input->getArgument('password');
        $userDto->firstname = $input->getOption('firstname');
        $userDto->lastname = $input->getOption('lastname');
        $userDto->roles = $input->getOption('roles');

        $io = new SymfonyStyle($input, $output);
        //Validate input values
        $errors = $this->validator->validate($userDto);
        if (count($errors) > 0) {
            $io->error(sprintf('%s: %s', $errors->get(0)->getPropertyPath(), $errors->get(0)->getMessage()));
            return Command::INVALID;
        }
        //Register new user
        $user = $userDto->createUserEntity();
        $user->setPassword($this->passwordHasher->hashPassword($user, $userDto->password));
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('User %s was created successfully', $userDto->email));

        return Command::SUCCESS;
    }
}

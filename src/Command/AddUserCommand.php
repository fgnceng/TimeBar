<?php


namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Utils\Validator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Stopwatch\Stopwatch;


class AddUserCommand extends Command
{
    protected static $defaultName = 'app:add-user';

    /**
     * @var SymfonyStyle
     */
    private $io;

    private $entityManager;
    private $passwordEncoder;
    private $validator;
    private $users;

    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $encoder, Validator $validator, UserRepository $users)
    {
        parent::__construct();

        $this->entityManager = $em;
        $this->passwordEncoder = $encoder;
        $this->validator = $validator;
        $this->users = $users;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addArgument('username', InputArgument::OPTIONAL, 'The username of the new user')
            ->addArgument('password', InputArgument::OPTIONAL, 'The plain password of the new user')
            ->addArgument('email', InputArgument::OPTIONAL, 'The email of the new user')
            ->addArgument('bulletin', null, InputOption::VALUE_NONE, 'The bulletin of the new user')
            ->addOption('roles', null, InputOption::VALUE_NONE, 'The roles of the new user');
    }


    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }


    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if ($input->getArgument('username') !== null && $input->getArgument('password') !== null && $input->getArgument('email') !== null) {
            return;
        }

        $this->io->title('Add User Command Page');


        $username = $input->getArgument('username');
        if ($username !== null) {
            $this->io->text(' > <info>Username</info>: ' . $username);
        } else {
            $username = $this->io->ask('Username', null, [$this->validator, 'validateUsername']);
            $input->setArgument('username', $username);
        }


        $password = $input->getArgument('password');
        if ($password == !null) {
            $this->io->text(' > <info>Password</info>: ' . str_repeat('*', mb_strlen($password)));
        } else {
            $password = $this->io->askHidden('Password (your type will be hidden)', [$this->validator, 'validatePassword']);
            $input->setArgument('password', $password);
        }

        $email = $input->getArgument('email');
        if ($email !== null) {
            $this->io->text(' > <info>Email</info>: ' . $email);
        } else {
            $email = $this->io->ask('Email', null, [$this->validator, 'validateEmail']);
            $input->setArgument('email', $email);
        }


    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $username = $input->getArgument('username');
        $plainPassword = $input->getArgument('password');
        $email = $input->getArgument('email');
        $bulletin = $input->getArgument('bulletin');
        $isAdmin = $input->getOption('roles');
        $this->validateUserData($username, $plainPassword, $email);//database'de var olup olmadıgı kontrol edildi.


        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setBulletin($bulletin);
        $user->setRoles([$isAdmin ? 'ROLE_ADMIN' : 'ROLE_USER']);


        $encodedPassword = $this->passwordEncoder->encodePassword($user, $plainPassword);
        $user->setPassword($encodedPassword); //password önce encode edilip sonra user a set edildi.

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->io->success(sprintf('%s was successfully created: %s (%s)', 'User', $user->getUsername(), $user->getEmail()));

    }

    private function validateUserData($username, $plainPassword, $email)
    {

        $existingUser = $this->users->findOneBy(['username' => $username]);

        if ($existingUser !== null) {
            throw new RuntimeException(sprintf('There is already a user registered with the "%s" username.', $username));
        }


        $this->validator->validatePassword($plainPassword);
        $this->validator->validateEmail($email);
        $existingEmail = $this->users->findOneBy(['email' => $email]);

        if ($existingEmail !== null) {
            throw new RuntimeException(sprintf('There is already a user registered with the "%s" email.', $email));
        }
    }

}

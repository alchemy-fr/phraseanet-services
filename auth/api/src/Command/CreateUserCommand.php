<?php

declare(strict_types=1);

namespace App\Command;

use App\Consumer\Handler\Notify\RegisterUserToNotifierHandler;
use App\User\UserManager;
use Arthem\Bundle\RabbitBundle\Consumer\Event\EventMessage;
use Arthem\Bundle\RabbitBundle\Producer\EventProducer;
use Exception;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateUserCommand extends Command
{
    private UserManager $userManager;
    private EventProducer $eventProducer;

    public function __construct(UserManager $userManager, EventProducer $eventProducer)
    {
        parent::__construct();

        $this->userManager = $userManager;
        $this->eventProducer = $eventProducer;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('app:user:create')
            ->setDescription('Creates a user')
            ->addArgument('username', InputArgument::REQUIRED, 'The username')
            ->addOption(
                'password',
                'p',
                InputOption::VALUE_REQUIRED,
                'Define a password (one is generated by default).',
                null
            )
            ->addOption(
                'update-if-exist',
                null,
                InputOption::VALUE_NONE,
                'If username already exists, just update the password.',
                null
            )
            ->addOption(
                'roles',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'User roles',
                null
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('User Credentials');
        $username = $input->getArgument('username');
        if (empty($username)) {
            throw new InvalidArgumentException('Missing or empty username');
        }

        $user = $this->userManager->findUserByUsername($username);
        if (null === $user) {
            $user = $this->userManager->createUser();
            $user->setUsername($username);
        } elseif (!$input->getOption('update-if-exist')) {
            throw new Exception(sprintf('User with username "%s" already exists', $username));
        }

        $user->setEnabled(true);

        if (null === $password = $input->getOption('password')) {
            $password = rtrim(str_replace('+', '.', base64_encode(random_bytes(32))), '=');
        }
        $user->setPlainPassword($password);

        if (null !== $roles = $input->getOption('roles')) {
            $user->setUserRoles($roles);
        }

        $headers = ['Username', 'Plain password'];
        $rows = [
            [$user->getUsername(), $user->getPlainPassword()],
        ];

        $this->userManager->encodePassword($user);
        $this->userManager->persistUser($user);
        $user->eraseCredentials();

        $this->eventProducer->publish(new EventMessage(RegisterUserToNotifierHandler::EVENT, [
            'id' => $user->getId(),
        ]));

        $io->table($headers, $rows);

        return 0;
    }
}

<?php

namespace App\Command;


use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCommand extends Command
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        parent::__construct();

        $this->connection = $connection;
    }

    protected function configure()
    {
        $this
            ->setName('app:clear')
            ->setDescription('Clears env')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->connection->prepare('TRUNCATE TABLE event')->execute();
        $this->connection->prepare('TRUNCATE TABLE player_projection')->execute();
        $this->connection->prepare('TRUNCATE TABLE player_bonus_projection')->execute();

        $output->writeln('Env is cleared');
    }
}
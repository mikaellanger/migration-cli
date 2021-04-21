<?php

namespace ByJG\DbMigration\Console;

use ByJG\DbMigration\Exception\DatabaseNotVersionedException;
use ByJG\DbMigration\Exception\OldVersionSchemaException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;

class InstallCommand extends ConsoleCommand
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('install')
            ->setDescription('Install or upgrade the migrate version in a existing database');

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            if (parent::execute($input, $output) != 0) {
                return 1;
            };

            $action = 'Database is already versioned. ';
            try {
                $this->migration->getCurrentVersion();
            } catch (DatabaseNotVersionedException $ex) {
                $action = 'Created the version table';
                $this->migration->createVersion();
            } catch (OldVersionSchemaException $ex) {
                $action = 'Updated the version table';
                $this->migration->updateTableVersion();
            }

            $version = $this->migration->getCurrentVersion();
            $output->writeln($action);
            $output->writeln('current version: ' . $version['version']);
            $output->writeln('current status.: ' . $version['status']);
            return 0;
        } catch (Exception $ex) {
            $this->handleError($ex, $output);
            return 1;
        }
    }
}

<?php

namespace RedCode\GitHook\Command;

use RedCode\GitHook\GitHookManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('redcode:precommit:install')
            ->setDescription('Install pre-commit hooks to the local system')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        (new GitHookManager($output))->install();
    }
}

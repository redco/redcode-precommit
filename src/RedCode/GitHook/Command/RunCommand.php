<?php

namespace RedCode\GitHook\Command;

use RedCode\GitHook\GitHookManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('redcode:precommit:run')
            ->setDescription('Run pre-commit hook')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return (new GitHookManager())->runHooks($output);
    }
}

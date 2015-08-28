<?php

namespace RedCode\GitHook\Process;

use RedCode\GitHook\GitHook;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractGitHookProcess
{
    /**
     * @param GitHook         $hook   Options about hook
     * @param OutputInterface $output
     * @param array           $files  Files to be committed
     *
     * @return int exit code
     */
    public function run(GitHook $hook, OutputInterface $output, array $files = [])
    {
        $files = $hook->getMatchFiles($files);
        if (empty($files)) {
            return 0;
        }

        $output->writeln(sprintf('<info>Checking %s</info>', $hook->getDescription()));

        return $this->execute($hook, $output, $files);
    }

    /**
     * @param GitHook         $hook   Options about hook
     * @param OutputInterface $output
     * @param array           $files  Files to be committed
     *
     * @return int exit code
     */
    abstract protected function execute(GitHook $hook, OutputInterface $output, array $files = []);
}

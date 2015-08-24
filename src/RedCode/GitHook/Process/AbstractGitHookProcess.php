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
    abstract public function run(GitHook $hook, OutputInterface $output, array $files = []);
}

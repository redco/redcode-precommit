<?php

namespace RedCode\GitHook\Process;

use RedCode\GitHook\GitHook;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\CS\Config\Config;
use Symfony\CS\FixerInterface;

class CommandProcess extends AbstractGitHookProcess
{
    private $command;

    public function __construct($command)
    {
        $this->command = $command;
    }

    /**
     * {@inheritdoc}
     */
    public function run(GitHook $hook, OutputInterface $output, array $files = [])
    {
        $exitCode = 0;
        $counter = 1;
        foreach ($files as $file) {
            $file = realpath($file);
            if (!$hook->match($file)) {
                continue;
            }

            $command = str_replace('%file%', $file, $this->command);
            $process = new Process($command);
            if ($processResult = $process->run()) {
                $processOutput = preg_replace('/Fixed.*\n/u', '', $process->getOutput());
                $processOutput = preg_replace('/1\)/u', sprintf('%s)', $counter++), $processOutput);
                preg_replace('/^Fixed.*\n/u', '', $process->getOutput());
                $output->write($processOutput);
                $exitCode |= $processResult;
            }
        }

        return $exitCode;
    }
}

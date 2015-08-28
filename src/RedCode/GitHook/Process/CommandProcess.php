<?php

namespace RedCode\GitHook\Process;

use RedCode\GitHook\GitHook;
use RedCode\GitHook\Process\Output\AbstractOutputWrapper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class CommandProcess extends AbstractGitHookProcess
{
    /**
     * @var string
     */
    private $command;

    /**
     * @var AbstractOutputWrapper
     */
    private $outputWrapper;

    public function __construct($command)
    {
        $this->command = $command;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(GitHook $hook, OutputInterface $output, array $files = [])
    {
        $exitCode = 0;
        foreach ($files as $file) {
            $absoluteFile = realpath($file);
            $command = str_replace('%file%', $absoluteFile, $this->command);
            $command = str_replace('%relativeFile%', $file, $command);
            $process = new Process($command);
            if ($processResult = $process->run()) {
                $processOutput = $process->getOutput();
                if ($this->outputWrapper instanceof AbstractOutputWrapper) {
                    $processOutput = $this->outputWrapper->getOutput($processOutput, $file);
                }
                $output->write($processOutput);
                $exitCode |= $processResult;
            }
        }

        return $exitCode;
    }

    /**
     * @param AbstractOutputWrapper $outputWrapper
     *
     * @return self
     */
    public function setOutputWrapper(AbstractOutputWrapper $outputWrapper)
    {
        $this->outputWrapper = $outputWrapper;

        return $this;
    }
}

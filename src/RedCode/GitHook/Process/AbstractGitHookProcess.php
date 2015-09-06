<?php

namespace RedCode\GitHook\Process;

use RedCode\GitHook\GitHook;
use RedCode\GitHook\OutputAwareInterface;
use RedCode\GitHook\OutputAwareTrait;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractGitHookProcess implements OutputAwareInterface
{
    use OutputAwareTrait;

    public function __construct(OutputInterface $output = null)
    {
        $this->setOutput($output);
    }

    /**
     * @param GitHook $hook  Options about hook
     * @param array   $files Files to be committed
     *
     * @return int exit code
     */
    public function run(GitHook $hook, array $files = [])
    {
        $files = $hook->getMatchFiles($files);
        if (empty($files)) {
            return 0;
        }

        $this->writeln(sprintf('Checking for %s', $hook->getDescription()), OutputAwareInterface::TYPE_INFO);

        return $this->execute($hook, $files);
    }

    /**
     * @param GitHook $hook  Options about hook
     * @param array   $files Files to be committed
     *
     * @return int exit code
     */
    abstract protected function execute(GitHook $hook, array $files = []);
}

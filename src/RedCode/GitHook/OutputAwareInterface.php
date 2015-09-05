<?php

namespace RedCode\GitHook;

use Symfony\Component\Console\Output\OutputInterface;

interface OutputAwareInterface
{
    const TYPE_INFO = __LINE__;
    const TYPE_ERROR = __LINE__;
    const TYPE_COMMENT = __LINE__;

    /**
     * @return OutputInterface
     */
    public function getOutput();

    /**
     * @param OutputInterface $output
     *
     * @return self
     */
    public function setOutput($output);

    /**
     * @param string $message
     * @param string $type
     *
     * @return self
     */
    public function writeln($message, $type = null);
}

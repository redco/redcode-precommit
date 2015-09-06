<?php

namespace RedCode\GitHook;

use Symfony\Component\Console\Output\OutputInterface;

trait OutputAwareTrait
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param OutputInterface $output
     *
     * @return self
     */
    public function setOutput($output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @param string $message
     * @param string $type
     *
     * @return self
     */
    public function writeln($message, $type = null)
    {
        if ($this->output) {
            static $templates = [
                OutputAwareInterface::TYPE_INFO => '<info>%s</info>',
                OutputAwareInterface::TYPE_ERROR => '<error>%s</error>',
                OutputAwareInterface::TYPE_COMMENT => '<comment>%s</comment>',
            ];
            $message = array_key_exists($type, $templates) ? sprintf($templates[$type], $message) : $message;
            $this->output->writeln($message);
        }

        return $this;
    }
}

<?php

namespace RedCode\GitHook\Process\Output;

class PhpCsOutputWrapper extends AbstractOutputWrapper
{
    private $fileCounter = 1;

    /**
     * {@inheritdoc}
     */
    public function getOutput($output, $file)
    {
        // Recovery standart output
        $output = preg_replace('/Fixed.*\n/u', '', $output);
        $output = preg_replace('/1\) (.+)\n/u', sprintf('%s) %s'.PHP_EOL, $this->fileCounter++, $file), $output);
        $output = preg_replace('/(----------.*)\n/u', '<comment>$1</comment>'.PHP_EOL, $output);
        $output = preg_replace('/\ ((---)|(-))\ /u', ' <error>$1</error> ', $output);
        $output = preg_replace('/\ (-)([A-z])/u', ' <error>$1</error>$2', $output);
        $output = preg_replace('/\ ((\+\+\+)|(\+))\ /u', ' <info>$1</info> ', $output);

        return $output;
    }
}

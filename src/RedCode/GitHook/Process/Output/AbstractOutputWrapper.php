<?php

namespace RedCode\GitHook\Process\Output;

abstract class AbstractOutputWrapper
{
    /**
     * @param string $output
     * @param string $file
     *
     * @return string
     */
    abstract public function getOutput($output, $file);
}

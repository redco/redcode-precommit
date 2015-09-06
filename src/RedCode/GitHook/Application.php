<?php

namespace RedCode\GitHook;

use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    public function __construct()
    {
        parent::__construct('Pre-commit utility', GitHook::VERSION);
        $this->add(new Command\InstallCommand());
        $this->add(new Command\UninstallCommand());
        $this->add(new Command\StatusCommand());
        $this->add(new Command\RunCommand());
    }

    public function getLongVersion()
    {
        $version = parent::getLongVersion().' by <comment>Andrew Reddikh</comment>';

        return $version;
    }
}

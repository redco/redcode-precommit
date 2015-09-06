<?php

namespace RedCode\GitHook\Process;

use RedCode\GitHook\GitHook;
use RedCode\GitHook\Process\Output\PhpCsOutputWrapper;
use Symfony\CS\Config\Config;
use Symfony\CS\FixerInterface;

class PhpCsProcess extends AbstractGitHookProcess
{
    /*
     * TODO: fix command to 'git show :%relativeFile% | ./bin/php-cs-fixer fix --diff -' after PR will be merged https://github.com/FriendsOfPHP/PHP-CS-Fixer/pull/1356
     */
    const COMMAND = './bin/php-cs-fixer fix --diff --dry-run %relativeFile%';

    /**
     * {@inheritdoc}
     */
    public function execute(GitHook $hook, array $files = [])
    {
        $config = null;
        if (file_exists($configFile = './.php_cs')) {
            $config = include $configFile;
            // verify that the config has an instance of Config
            if (!$config instanceof Config) {
                throw new \UnexpectedValueException(
                    sprintf(
                        'The config file "%s" does not return a "Symfony\CS\Config\Config" instance. Got: "%s".',
                        $configFile,
                        is_object($config) ? get_class($config) : gettype($config)
                    )
                );
            }
        }
        $this->showDescription($config);

        return (new CommandProcess(self::COMMAND))
            ->setOutput($this->getOutput())
            ->setOutputWrapper(new PhpCsOutputWrapper())
            ->execute($hook, $files);
    }

    /**
     * @param Config $config
     */
    private function showDescription(Config $config = null)
    {
        if ($config) {
            $this->writeln(
                sprintf(
                    'Level: <comment>%s</comment>, Fixers: <comment>%s</comment>',
                    $this->getLevelName($config),
                    implode(', ', $config->getFixers())
                )
            );
        }
    }

    /**
     * Returns level name from config.
     *
     * @param Config $config
     *
     * @return string
     */
    private function getLevelName(Config $config)
    {
        static $map = [
            FixerInterface::PSR0_LEVEL => 'PSR0',
            FixerInterface::PSR1_LEVEL => 'PSR1',
            FixerInterface::PSR2_LEVEL => 'PSR2',
            FixerInterface::SYMFONY_LEVEL => 'Symfony',
            FixerInterface::CONTRIB_LEVEL => 'Contrib',
        ];

        return $map[$config->getLevel()];
    }
}

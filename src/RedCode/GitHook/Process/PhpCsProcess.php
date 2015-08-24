<?php

namespace RedCode\GitHook\Process;

use RedCode\GitHook\GitHook;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\CS\Config\Config;
use Symfony\CS\FixerInterface;

class PhpCsProcess extends AbstractGitHookProcess
{
    const COMMAND = './bin/php-cs-fixer fix --diff --dry-run %file%';

    /**
     * {@inheritdoc}
     */
    public function run(GitHook $hook, OutputInterface $output, array $files = [])
    {
        $config = null;
        if (file_exists($configFile = './.php_cs')) {
            $config = include $configFile;
            // verify that the config has an instance of Config
            if (!$config instanceof Config) {
                throw new \UnexpectedValueException(sprintf('The config file "%s" does not return a "Symfony\CS\Config\Config" instance. Got: "%s".',
                    $configFile, is_object($config) ? get_class($config) : gettype($config)));
            }
        }
        $this->showDescription($output, $config);

        return (new CommandProcess(self::COMMAND))->run($hook, $output, $files);
    }

    /**
     * @param OutputInterface $output
     * @param Config $config
     */
    private function showDescription(OutputInterface $output, Config $config = null)
    {
        $output->writeln("<info>Checking PHP Code Style</info>");
        if ($config) {
            $output->writeln(
                sprintf(
                    'Level: <comment>%s</comment>, Fixers: <comment>%s</comment>',
                    $this->getLevelName($config),
                    implode(', ', $config->getFixers())
                )
            );
        }
    }

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

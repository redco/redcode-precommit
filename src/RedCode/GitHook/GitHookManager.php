<?php

namespace RedCode\GitHook;

use RedCode\GitHook\Process\AbstractGitHookProcess;
use RedCode\GitHook\Process\CommandProcess;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class GitHookManager
{
    const CONFIG_FILE_NAME = '.pre-commit.yaml';
    const PRE_COMMIT_HOOK_LINK = '.git/hooks/pre-commit';
    const PRE_COMMIT_HOOK_FILE = '/../../../pre-commit';

    public function __construct()
    {
        chdir($this->getRootDir());
    }

    public function runHooks(OutputInterface $output)
    {
        $files = $this->getCommittedFiles();
        $hooks = $this->getHooks(true);
        $exitCode = 0;

        foreach ($hooks as $hook) {
            $customProcess = $hook->getClass();
            $customProcess = $customProcess && class_exists($customProcess) ?
                new $customProcess() :
                null;

            if ($hook->getClass()) {
                if (!$customProcess instanceof AbstractGitHookProcess) {
                    throw new \UnexpectedValueException(
                        sprintf('Class %s must extends AbstractGitHookProcess', $hook->getClass())
                    );
                }

                $process = $customProcess;
            } else {
                $process = new CommandProcess($hook->getScript());
            }
            $output->writeln(sprintf('<info>Checking %s</info>', $hook->getDescription()));
            $exitCode |= $process->run($hook, $output, $files);
        }
        if ($exitCode) {
            $output->writeln('<error>Before commit you have to fix above errors</error>');
        }

        return $exitCode;
    }

    public function installHooks(OutputInterface $output)
    {
        if ($this->installationStatus()) {
            $output->writeln('You already have installed hooks');

            return false;
        }

        $process = new Process(
            sprintf('ln -s %s %s', __DIR__.'/../../../pre-commit', './.git/hooks/pre-commit')
        );
        $process->run();
        if ($process->isSuccessful()) {
            $output->writeln('pre-commit hooks are successfully installed');
        } else {
            $output->writeln('Errors occurred during installation');
            $output->writeln($process->getOutput());
        }
    }

    /**
     * @param OutputInterface $output
     *
     * @return bool
     */
    public function installationStatus(OutputInterface $output = null)
    {
        if (file_exists($file = self::PRE_COMMIT_HOOK_LINK)) {
            $process = new Process(sprintf('readlink %s', $file));
            if (!$process->run()) {
                $realFile = realpath(trim($process->getOutput()));
                $preCommitFile = realpath(__DIR__.self::PRE_COMMIT_HOOK_FILE);
                if ($realFile !== $preCommitFile) {
                    $output && $output->writeln('Some other pre-commit hook has installed');
                    $output && $output->writeln('<comment>You need to uninstall them first</comment>');

                    return true;
                }

                $output && $output->writeln('<info>Installed hooks</info>');
                foreach ($this->getHooks(true) as $hook) {
                    $output && $output->writeln(sprintf(' * <comment>%s</comment>', $hook->getId()));
                }

                return true;
            }
        }

        $output && $output->writeln('<info>Hooks are not installed</info>');

        return false;
    }

    public function uninstallHooks(OutputInterface $output)
    {
        if (!$this->installationStatus()) {
            $output->writeln('<info>Hooks are not installed</info>');
        }

        $process = new Process(
            sprintf('rm %s', self::PRE_COMMIT_HOOK_LINK)
        );
        $process->run();
        if ($process->isSuccessful()) {
            $output->writeln('pre-commit hooks are successfully removed');
        } else {
            $output->writeln('Errors occurred during uninstall process');
            $output->writeln($process->getOutput());
        }
    }

    /**
     * @return array
     */
    public function getCommittedFiles()
    {
        $process = new Process('git diff --cached --name-only --diff-filter=ACMR');
        $process->run();

        return array_filter(explode("\n", $process->getOutput()));
    }

    /**
     * @param bool $activeOnly
     *
     * @return GitHook[]
     */
    public function getHooks($activeOnly = false)
    {
        $hooks = [];
        $config = $this->getConfig();
        foreach ($config['hooks'] as $id => $item) {
            $hookStatus = in_array($id, $config['active']) ?
                GitHook::STATUS_ACTIVE :
                GitHook::STATUS_NOT_ACTIVE;

            if ($activeOnly && $hookStatus !== GitHook::STATUS_ACTIVE) {
                continue;
            }

            $hooks[$id] = $hook = (new GitHook())
                ->setId($id)
                ->setStatus($hookStatus);
            if (!empty($item['script'])) {
                $hook->setScript($item['script']);
            }
            if (!empty($item['class'])) {
                $hook->setClass($item['class']);
            }
            if (!empty($item['description'])) {
                $hook->setDescription($item['description']);
            }
            if (!empty($item['file_types'])) {
                $types = array_filter(explode(',', $item['file_types']), 'trim');
                foreach ($types as $type) {
                    $hook->addFileType($type);
                }
            }
            if (!empty($item['paths']) && is_array($item['paths'])) {
                foreach ($item['paths'] as $path) {
                    $hook->addPath($path);
                }
            }
        }

        return $hooks;
    }

    /**
     * @return array
     */
    private function getConfig()
    {
        $yaml = new Yaml();

        $configs = [];
        // local config
        if (file_exists($file = __DIR__.'/../../../'.self::CONFIG_FILE_NAME)) {
            $configs[] = $yaml->parse($file);
        }
        // project config
        if (file_exists($file = __DIR__.'/../../../../../../'.self::CONFIG_FILE_NAME)) {
            $configs[] = $yaml->parse($file);
        }

        $config = [
            'active' => [],
            'hooks' => [],
        ];
        foreach ($configs as $configInfo) {
            $config = array_merge_recursive($config, $configInfo);
            $config['active'] = array_unique($config['active']);
        }

        return $config;
    }

    /**
     * @return string
     */
    private function getRootDir()
    {
        if ($file = file_exists(__DIR__.'/../../../../../autoload.php')) {
            return realpath(__DIR__.'/../../../../../../');
        }

        return realpath(__DIR__.'/../../../');
    }
}

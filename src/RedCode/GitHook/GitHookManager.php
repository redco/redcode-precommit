<?php

namespace RedCode\GitHook;

use RedCode\GitHook\Process\AbstractGitHookProcess;
use RedCode\GitHook\Process\CommandProcess;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class GitHookManager implements OutputAwareInterface
{
    use OutputAwareTrait;

    const CONFIG_FILE_NAME = '.pre-commit.yaml';
    const PRE_COMMIT_HOOK_LINK = '.git/hooks/pre-commit';
    const PRE_COMMIT_HOOK_FILE = '/../../../pre-commit';

    public function __construct(OutputInterface $output = null)
    {
        $this->setOutput($output);
        chdir($this->getRootDir());
    }

    /**
     * Execute all active pre-commit hooks.
     *
     * @return int
     */
    public function run()
    {
        $files = $this->getCommittedFiles();
        $hooks = $this->getHooks(true);
        $exitCode = 0;

        foreach ($hooks as $hook) {
            $process = $hook->getClass();
            $process = $process && class_exists($process) ?
                new $process() :
                null;

            if ($hook->getClass()) {
                if (!$process instanceof AbstractGitHookProcess) {
                    throw new \UnexpectedValueException(
                        sprintf('Class %s must extends AbstractGitHookProcess', $hook->getClass())
                    );
                }
            } else {
                $process = new CommandProcess($hook->getScript());
            }
            if ($process instanceof OutputAwareInterface) {
                $process->setOutput($this->getOutput());
            }
            $exitCode |= $process->run($hook, $files);
        }
        if ($exitCode) {
            $this->writeln(
                'Something went wrong. You need to fix above errors before committing',
                OutputAwareInterface::TYPE_ERROR
            );
        }

        return $exitCode;
    }

    /**
     * Returns true when has installed successfully, false otherwise.
     *
     * @return bool
     */
    public function install()
    {
        if ($this->isInstalled(true)) {
            $this->writeln('You already have installed hooks', OutputAwareInterface::TYPE_COMMENT);

            return false;
        }

        $process = new Process(
            sprintf('ln -s %s %s', __DIR__.'/../../../pre-commit', './.git/hooks/pre-commit')
        );
        $process->run();
        if ($process->isSuccessful()) {
            $this->writeln('Pre-commit hook has been successfully installed', OutputAwareInterface::TYPE_INFO);
        } else {
            $this->writeln('Some errors occurred during the installation process', OutputAwareInterface::TYPE_ERROR);
            $this->writeln($process->getOutput());
        }
    }

    /**
     * Return true if it was successfuly installed, false otherwise.
     *
     * @param bool $silentMode
     *
     * @return bool
     */
    public function isInstalled($silentMode = false)
    {
        if (file_exists($file = self::PRE_COMMIT_HOOK_LINK)) {
            $process = new Process(sprintf('readlink %s', $file));
            if (!$process->run()) {
                $realFile = realpath(trim($process->getOutput()));
                $preCommitFile = realpath(__DIR__.self::PRE_COMMIT_HOOK_FILE);
                if ($realFile !== $preCommitFile) {
                    if (!$silentMode) {
                        $this->writeln('Another pre-commit hook is installed', OutputAwareInterface::TYPE_ERROR);
                        $this->writeln('You need to uninstall it first', OutputAwareInterface::TYPE_COMMENT);
                    }

                    return true;
                }

                if (!$silentMode) {
                    $this->writeln('Next pre-commit hooks are installed:', OutputAwareInterface::TYPE_INFO);
                    foreach ($this->getHooks(true) as $hook) {
                        $this->writeln(
                            sprintf(' * %s', $hook->getId()),
                            OutputAwareInterface::TYPE_COMMENT
                        );
                    }
                }

                return true;
            }
        }
        if (!$silentMode) {
            $this->writeln('Pre-commit hook is not installed', OutputAwareInterface::TYPE_COMMENT);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        if (!$this->isInstalled(true)) {
            $this->writeln('Pre-commit hook is not installed.', OutputAwareInterface::TYPE_COMMENT);

            return false;
        }

        $process = new Process(
            sprintf('rm %s', self::PRE_COMMIT_HOOK_LINK)
        );
        $process->run();
        if ($process->isSuccessful()) {
            $this->writeln('Pre-commit hook has been successfully removed', OutputAwareInterface::TYPE_INFO);
        } else {
            $this->writeln('Some errors occurred during the uninstall process', OutputAwareInterface::TYPE_ERROR);
            $this->writeln($process->getOutput());
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

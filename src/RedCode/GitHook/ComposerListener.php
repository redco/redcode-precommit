<?php

namespace RedCode\GitHook;

use Composer\Script\Event;

class ComposerListener
{
    public static function postInstall(Event $event)
    {
        self::install($event);
    }

    public static function postUpdate(Event $event)
    {
        self::install($event);
    }

    private static function install(Event $event)
    {
        if (!$event->isDevMode()) {
            return;
        }
        $manager = new GitHookManager();

        if ($manager->isInstalled() || $manager->install()) {
            $event->getIO()->write('Pre-commit hook has been successfully installed.');
        } else {
            $event->getIO()->writeError(
                'Some errors occurred during the installation process. Pre-commit hook are NOT installed.'
            );
        }
    }
}

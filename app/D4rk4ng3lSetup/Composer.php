<?php

namespace D4rk4ng3lSetup;

use Composer\Script\CommandEvent;

class Composer
{
    public static function hookRootPackageInstall(CommandEvent $event)
    {
        $event->getComposer()->getEventDispatcher()->addSubscriber(new InstallationHelper());
    }
}

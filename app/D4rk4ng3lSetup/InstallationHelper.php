<?php

namespace D4rk4ng3lSetup;

use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Script\CommandEvent;
use Composer\Script\ScriptEvents;
use D4rk4ng3lSetup\Filesystem\Filesystem;
use D4rk4ng3lSetup\Yaml\Yaml;
use Symfony\Component\HttpFoundation\File\File;

class InstallationHelper implements EventSubscriberInterface
{
    protected static $paths;

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     * * The method name to call (priority defaults to 0)
     * * An array composed of the method name to call and the priority
     * * An array of arrays composed of the method names to call and respective
     *   priorities, or 0 if unset
     *
     * For instance:
     *
     * * array('eventName' => 'methodName')
     * * array('eventName' => array('methodName', $priority))
     * * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            ScriptEvents::POST_INSTALL_CMD => array(
                array('setupDirectories', 512),
                array('setupConfiguration', 256),
                array('removeGitStuff', 128),
                array('removeSetup', 0)
            ),
        );
    }

    public static function setupDirectories(CommandEvent $event)
    {
        $fs = new Filesystem();
        $fs->mkdir(static::getPath());
    }

    public static function setupConfiguration(CommandEvent $event)
    {
        $oxidPath = $event->getIO()->ask('Path to your OXID eShop installation: ');

        $configPath = static::getPath('config');

        $targetFile = $configPath . '/parameters.yml';

        $initialConfig = array(
            'parameters' => array(
                'path.module' => static::getPath('source'),
                'path.oxid' => $oxidPath,
            )
        );

        $fs = new Filesystem();
        $fs->dumpFile($targetFile, Yaml::dump($initialConfig));
    }

    public static function removeGitStuff(CommandEvent $event)
    {
        if ($event->getIO()->askConfirmation('Remove the GIT related stuff? [Y/n] ')) {
            $fs = new Filesystem();
            foreach (static::getPath() as $path) {
                $filename = $path . '/.gitkeep';
                if ($fs->exists($filename)) {
                    $fs->remove($filename);
                }
            }

            $fs->remove(static::getPath('root') . '/.git');
        }
    }

    public static function removeSetup(CommandEvent $event)
    {
        $fs = new Filesystem();
        $fs->remove(__DIR__);
    }

    /**
     * @param string $type
     *
     * @return bool|array|string
     */
    private static function getPath($type = null)
    {
        if (null === static::$paths) {
            $rootDir = getcwd();
            $appDir  = $rootDir . '/app';
            $srcDir  = $rootDir . '/src';
            $venDir  = $rootDir . '/vendor';

            static::$paths = array(
                'root'   => $rootDir,
                'app'    => $appDir,
                'config' => $appDir . '/config',
                'cache'  => $appDir . '/cache',
                'source' => $srcDir,
                'vendor' => $venDir,
            );
        }

        if (null === $type) {
            return static::$paths;
        }

        if (!isset(static::$paths[$type])) {
            return false;
        }

        return static::$paths[$type];
    }
}

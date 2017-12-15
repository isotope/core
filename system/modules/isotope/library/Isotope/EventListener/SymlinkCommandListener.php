<?php

namespace Isotope\EventListener;

use Contao\CoreBundle\Command\SymlinksCommand;
use Contao\CoreBundle\Util\SymlinkUtil;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Filesystem\Filesystem;

class SymlinkCommandListener
{
    /**
     * @var string
     */
    private $rootDir;

    /**
     * Constructor.
     *
     * @param string $rootDir
     */
    public function __construct($rootDir)
    {
        $this->rootDir = dirname($rootDir);
    }

    /**
     * Adds the isotope symlink.
     *
     * @param ConsoleTerminateEvent $event
     */
    public function onConsoleTerminate(ConsoleTerminateEvent $event)
    {
        if (!($event->getCommand() instanceof SymlinksCommand) || $event->getExitCode() > 0) {
            return;
        }

        (new Filesystem())->mkdir($this->rootDir . '/isotope');

        SymlinkUtil::symlink('isotope', 'web/isotope', $this->rootDir);
    }
}

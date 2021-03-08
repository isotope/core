<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

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
    private $projectDir;

    /**
     * Constructor.
     *
     * @param string $projectDir
     */
    public function __construct($projectDir)
    {
        $this->projectDir = $projectDir;
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

        (new Filesystem())->mkdir($this->projectDir . '/isotope');

        SymlinkUtil::symlink('isotope', 'web/isotope', $this->projectDir);
    }
}

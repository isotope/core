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

use Contao\CoreBundle\Event\GenerateSymlinksEvent;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\PathUtil\Path;

/**
 * Adds a symlink from isotope images directory to the public web dir.
 */
class SymlinkListener
{
    /**
     * @var string
     */
    private $projectDir;

    /**
     * @var string
     */
    private $webDir;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(string $projectDir, string $webDir, Filesystem $filesystem = null)
    {
        $this->projectDir = $projectDir;
        $this->webDir = $webDir;
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    public function __invoke(GenerateSymlinksEvent $event)
    {
        $webDir = $this->filesystem->makePathRelative($this->webDir, $this->projectDir);

        if (!file_exists($isotopeDir = Path::join($this->projectDir, 'isotope'))) {
            $this->filesystem->mkdir($isotopeDir);
        }

        $event->addSymlink('isotope', Path::join($webDir, 'isotope'));
    }
}

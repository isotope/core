<?php

namespace Isotope\CoreBundle\EventListener;

use Contao\CoreBundle\Event\GenerateSymlinksEvent;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

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

        if (!$this->filesystem->exists($isotopeDir = Path::join($this->projectDir, 'isotope'))) {
            $this->filesystem->mkdir($isotopeDir);
        }

        $event->addSymlink('isotope', Path::join($webDir, 'isotope'));
    }
}

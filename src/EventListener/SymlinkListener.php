<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener;

use Contao\CoreBundle\Event\GenerateSymlinksEvent;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

/**
 * Adds a symlink from isotope images directory to the public web dir.
 */
#[AsEventListener('contao.generate_symlinks')]
#[Autoconfigure(bind: ['$projectDir' => '%kernel.project_dir%', '$webDir' => '%contao.web_dir%'])]
class SymlinkListener
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(
        private readonly string $projectDir,
        private readonly string $webDir,
        Filesystem|null $filesystem = null,
    ) {
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    public function __invoke(GenerateSymlinksEvent $event): void
    {
        $webDir = $this->filesystem->makePathRelative($this->webDir, $this->projectDir);

        if (!$this->filesystem->exists($isotopeDir = Path::join($this->projectDir, 'isotope'))) {
            $this->filesystem->mkdir($isotopeDir);
        }

        $event->addSymlink('isotope', Path::join($webDir, 'isotope'));
    }
}

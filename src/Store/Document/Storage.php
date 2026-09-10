<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Document;

use Contao\CoreBundle\Filesystem\VirtualFilesystemException;
use Contao\CoreBundle\Filesystem\VirtualFilesystemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
class Storage
{
    public function __construct(
        #[Autowire(service: 'contao.filesystem.virtual.isotope_documents')] private readonly VirtualFilesystemInterface $filesystem,
    ) {
    }

    /**
     * @param resource|string $contents
     */
    public function store($contents): string
    {
        $id = date('Ymd').'/'.Uuid::v4()->toRfc4122();

        if (\is_resource($contents)) {
            $this->filesystem->writeStream($id, $contents);
        } else {
            $this->filesystem->write($id, $contents);
        }

        return $id;
    }

    public function has(string $id): bool
    {
        try {
            return $this->filesystem->has($id);
        } catch (VirtualFilesystemException) {
            return false;
        }
    }

    /**
     * @return resource|null
     */
    public function retrieve(string $id)
    {
        try {
            return $this->filesystem->readStream($id);
        } catch (VirtualFilesystemException) {
            return null;
        }
    }
}

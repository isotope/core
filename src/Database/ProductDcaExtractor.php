<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Database;

use Contao\Controller;
use Contao\DcaExtractor;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

class ProductDcaExtractor extends DcaExtractor
{
    /**
     * @noinspection MagicMethodsValidityInspection
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(
        private readonly Filesystem $filesystem,
        #[Autowire(param: 'kernel.cache_dir')] private readonly string $cacheDir,
    ) {
        $this->strTable = 'tl_iso_product';
    }

    public function updateCache(): void
    {
        Controller::loadDataContainer($this->strTable);

        $this->dumpCacheFile();
    }

    public function deleteFromCache(string $fieldName): void
    {
        Controller::loadDataContainer($this->strTable);

        unset(
            $GLOBALS['TL_DCA'][$this->strTable]['fields'][$fieldName],
            $GLOBALS['TL_DCA'][$this->strTable]['config']['sql']['keys'][$fieldName],
        );

        $this->dumpCacheFile();
    }

    public static function getInstance($strTable = null): void
    {
        throw new \RuntimeException('Cannot create instance of '.self::class.', use the service instead');
    }

    /**
     * Write DcaExtractor config to cache file.
     */
    private function dumpCacheFile(): void
    {
        $this->createExtract();

        $this->filesystem->dumpFile(
            Path::join($this->cacheDir, 'contao/sql', $this->strTable.'.php'),
            \sprintf(
                "<?php\n\n%s\n\n%s\n\n%s\n\n%s\n\n%s\n\n%s\n\n\$this->blnIsDbTable = true;\n",
                \sprintf('$this->arrMeta = %s;', var_export($this->getMeta(), true)),
                \sprintf('$this->arrFields = %s;', var_export($this->getFields(), true)),
                \sprintf('$this->arrUniqueFields = %s;', var_export($this->getUniqueFields(), true)),
                \sprintf('$this->arrKeys = %s;', var_export($this->getKeys(), true)),
                \sprintf('$this->arrRelations = %s;', var_export($this->getRelations(), true)),
                \sprintf('$this->arrEnums = %s;', var_export($this->getEnums(), true)),
            ),
        );

        unset(parent::$arrInstances[$this->strTable]);
    }
}

<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Cron;

use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCronJob;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Remove MediaManager files that are no longer referenced by any product.
 */
#[AsCronJob('monthly')]
class PurgeMediaCron
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly Connection $connection,
        private readonly Filesystem $filesystem,
        private readonly LoggerInterface $contaoCronLogger,
        #[Autowire(param: 'kernel.project_dir')] private readonly string $projectDir,
    ) {
    }

    public function __invoke(): void
    {
        $directory = $this->projectDir.'/isotope';

        if (!is_dir($directory) || is_link($directory)) {
            return;
        }

        $this->framework->initialize();
        $this->framework->getAdapter(Controller::class)->loadDataContainer('tl_iso_product');

        $fields = array_keys(array_filter(
            $GLOBALS['TL_DCA']['tl_iso_product']['fields'],
            static fn (array $field) => 'mediaManager' === ($field['inputType'] ?? null) && isset($field['sql']),
        ));

        if ([] === $fields) {
            return;
        }

        $usedFiles = [];
        $columns = array_map($this->connection->quoteIdentifier(...), $fields);

        // Read all records, including variants, translations and unpublished products,
        // before deleting anything. A failed query must never cause files to be removed.
        foreach ($this->connection->iterateAssociative('SELECT '.implode(', ', $columns).' FROM tl_iso_product') as $product) {
            foreach ($product as $value) {
                foreach (StringUtil::deserialize($value, true) as $file) {
                    if (\is_array($file) && \is_string($file['src'] ?? null) && '' !== $file['src']) {
                        $usedFiles[basename($file['src'])] = true;
                    }
                }
            }
        }

        // Allow time for newly uploaded files to be saved to a product.
        $cutoff = time() - 86400;
        $total = 0;

        // MediaManager stores files directly in single-character subdirectories.
        // Leave root files (e.g. .htaccess) and unrelated directories untouched.
        $finder = Finder::create()
            ->files()
            ->in($directory)
            ->depth('== 1')
            ->path('~^[^/]/~') // only allows directories with one character name
        ;

        foreach ($finder as $file) {
            if (
                $file->isLink()
                || isset($usedFiles[$file->getFilename()])
                || $file->getMTime() >= $cutoff
            ) {
                continue;
            }

            $this->filesystem->remove($file->getPathname());
            ++$total;
        }

        $this->contaoCronLogger->info('Deleted '.$total.' unused MediaManager files.');
    }
}

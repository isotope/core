<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Product;

use Codefog\HasteBundle\Formatter;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\DataContainer;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Model\ProductTypeModel;
use Isotope\CoreBundle\Model\TaxClassModel;
use Isotope\CoreBundle\Product\DefaultPriceFactory;
use Isotope\CoreBundle\Store\Context\StoreContextFactory;
use Isotope\CoreBundle\Store\Price\MoneyFormat;
use Isotope\CoreBundle\Store\Price\PriceDefinitionCollection;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCallback('tl_iso_product', 'list.label.label')]
class LabelListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly Studio $imageStudio,
        private readonly Formatter $formatter,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TranslatorInterface $translator,
        private readonly DefaultPriceFactory $priceFactory,
        private readonly StoreContextFactory $storeContextFactory,
    ) {
    }

    public function __invoke(array $row, string $label, DataContainer $dc, array|string $args): array|string
    {
        // Deleting a product
        if (!\is_array($args)) {
            $args = [];
        }

        foreach ($GLOBALS['TL_DCA']['tl_iso_product']['list']['label']['fields'] as $i => $field) {
            switch ($field) {
                case 'images':
                    $args[$i] = $this->generateImage($row);
                    break;

                case 'name':
                    $args[$i] = $this->generateName($row, $dc);
                    break;

                case 'price':
                    $args[$i] = $this->generatePrice($row);
                    break;

                case 'variantFields':
                    $args[$i] = $this->generateVariantFields($row);
                    break;

                default:
                    $objProductType = ProductTypeModel::findById($row['type']);
                    if (
                        $objProductType
                        && $objProductType->hasVariants()
                        && !\in_array($field, $objProductType->getAttributes(), true)
                        && \in_array($field, $objProductType->getVariantAttributes(), true)
                    ) {
                        $values = $this->connection->fetchFirstColumn("SELECT $field FROM tl_iso_product WHERE pid=? AND language=''", [$row['id']]);
                        $values = array_unique(array_filter($values));
                        $removed = array_splice($values, 3);

                        $args[$i] = implode('<br>', $values);

                        if ($removed) {
                            $args[$i] .= '<br><span title="'.StringUtil::specialchars(implode(', ', $removed)).'">…</span><span class="invisible">'.StringUtil::specialchars(implode(', ', $removed)).'</span>';
                        }
                    } else {
                        $args[$i] ??= $row[$field] ?? null;
                    }
            }
        }

        return $args;
    }

    public function generateImage(array $row): string
    {
        $arrImages = StringUtil::deserialize($row['images']);

        if (!empty($arrImages) && \is_array($arrImages)) {
            $figureBuilder = $this->imageStudio->createFigureBuilder()->setSize([50, 50, 'proportional']);

            foreach ($arrImages as $image) {
                $path = 'isotope/'.strtolower(substr((string) $image['src'], 0, 1)).'/'.$image['src'];
                $figure = $figureBuilder->fromPath($path)->buildIfResourceExists();

                if ($figure) {
                    $script = \sprintf(
                        "Backend.openModalImage({'width':%s,'title':'%s','url':'%s'});return false",
                        $figure->getImage()->getOriginalDimensions()->getSize()->getWidth(),
                        str_replace("'", "\\'", $row['name']),
                        $path,
                    );

                    return \sprintf(
                        '<a href="%s" onclick="%s"><img src="%s" alt="%s"></a>',
                        $path,
                        $script,
                        $figure->getImage()->getImageSrc(),
                        $image['alt'] ?? '',
                    );
                }
            }
        }

        return '&nbsp;';
    }

    /**
     * Generate name label for product with link to variants if enabled.
     */
    private function generateName(array $row, DataContainer $dc): string
    {
        if ($dc->currentPid) {
            return $row['name'];
        }

        return \sprintf(
            '<a href="%s" target="_blank" title="%s">%s</a>',
            $this->urlGenerator->generate('contao_backend_preview', ['product' => $row['id']]),
            StringUtil::specialchars($this->translator->trans('MSC.view', [], 'contao_default')),
            $row['name'],
        );
    }

    /**
     * Generate price label for product.
     */
    private function generatePrice(array $row): string
    {
        $priceDefinition = null;
        $fromPrice = false;
        $context = $this->storeContextFactory->createBackend();

        if (0 === $row['pid']) {
            $type = ProductTypeModel::findById($row['type']);
        } else {
            $type = ProductTypeModel::findById($this->connection->fetchOne('SELECT type FROM tl_iso_product WHERE id=?', [$row['pid']]));
        }

        if (
            0 === $row['pid']
            && $type
            && $type->hasVariants()
            && \in_array('price', $type->getVariantAttributes(), true)
        ) {
            $variants = $this->connection->fetchAllAssociative(
                "SELECT * FROM tl_iso_product WHERE pid=? AND language=''",
                [$row['id']],
            );

            if (empty($variants)) {
                return '';
            }

            $data = array_map(fn (array $variant) => $this->priceFactory->createFromArray($variant, $type->hasAdvancedPrices(), $context), $variants);

            /** @var PriceDefinitionCollection $prices */
            $prices = (new PriceDefinitionCollection())->merge(...$data);

            $priceDefinition = $prices->getLowest();
            $fromPrice = $prices->hasRange();
        }

        if (null === $priceDefinition) {
            $prices = $this->priceFactory->createFromArray($row, $type?->hasAdvancedPrices() ?? false, $context);
            $priceDefinition = $prices->getLowest();
            $fromPrice = $prices->hasRange();
        }

        if (null !== $priceDefinition) {
            $label = (new MoneyFormat())->format($priceDefinition->getAmount());

            if ($fromPrice) {
                $label = $this->translator->trans('MSC.priceRangeLabel', [$label], 'contao_default');
            }

            $taxClass = $priceDefinition->getTaxClassId() ? TaxClassModel::findById($priceDefinition->getTaxClassId()) : null;

            return $label.\sprintf(' <span style="color:#999">[%s]</span>', $taxClass->name ?? $this->translator->trans('MSC.taxFree', [], 'contao_default'));
        }

        return '';
    }

    /**
     * Generate variant fields for product.
     */
    private function generateVariantFields(array $row): string
    {
        $attributes = [];

        foreach ($GLOBALS['TL_DCA']['tl_iso_product']['list']['label']['variantFields'] as $variantField) {
            $attributes[] = \sprintf(
                '<strong>%s:</strong>&nbsp;%s',
                $this->formatter->dcaLabel('tl_iso_product', $variantField),
                $this->formatter->dcaValue('tl_iso_product', $variantField, $row[$variantField] ?? null),
            );
        }

        return \sprintf('<div style="line-height: 1.3">%s</div>', implode('<br>', $attributes));
    }
}

<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Product;

use Codefog\HasteBundle\Formatter;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\DataContainer;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Model\ProductType;
use Isotope\Model\ProductPrice;
use Isotope\Model\TaxClass;
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
    ) {
    }

    public function __invoke(array $row, string $label, DataContainer $dc): array
    {
        $args = [];

        foreach ($GLOBALS['TL_DCA'][$dc->table]['list']['label']['fields'] as $i => $field) {
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
                    $args[$i] = $this->generateVariantFields($row, $dc);
                    break;

                default:
                    $objProductType = ProductType::findById($row['type']);
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
                        $args[$i] = $this->formatter->dcaValue('tl_iso_product', $field, $row[$field] ?? null);
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
        $objPrice = null;
        $fromPrice = false;

        if (
            0 === $row['pid']
            && null !== ($objProductType = ProductType::findById($row['type']))
            && $objProductType->hasVariants()
            && \in_array('price', $objProductType->getVariantAttributes(), true)
        ) {
            $variantIds = $this->connection->fetchFirstColumn(
                "SELECT id FROM tl_iso_product WHERE pid=? AND language=''",
                [$row['id']],
            );

            if (empty($variantIds)) {
                return '';
            }

            $objPrices = ProductPrice::findPrimaryByProductIds($variantIds);

            if (null !== $objPrices) {
                $fltPrice = null;
                $arrPrices = [];

                /** @var ProductPrice $price */
                foreach ($objPrices as $price) {
                    $fltNew = $price->getValueForTier($price->getLowestTier());
                    $arrPrices[] = $fltNew;

                    if (null === $fltPrice || $fltNew < $fltPrice) {
                        $fltPrice = $fltNew;
                        $objPrice = $price;
                    }
                }

                if (\count(array_unique($arrPrices)) > 1) {
                    $fromPrice = true;
                }
            }
        }

        if (null === $objPrice) {
            $objPrice = ProductPrice::findPrimaryByProductId($row['id']);
        }

        if (null !== $objPrice) {
            try {
                /** @var TaxClass $objTax */
                $objTax = $objPrice->getRelated('tax_class');
                $strTax = (null === $objTax ? '' : ' ('.$objTax->getName().')');

                if ($fromPrice) {
                    return $this->translator->trans('MSC.priceRangeLabel', [$objPrice->getValueForTier(1)], 'contao_default').$strTax;
                }

                return $objPrice->getValueForTier(1).$strTax;
            } catch (\Exception) {
                return '';
            }
        }

        return '';
    }

    /**
     * Generate variant fields for product.
     */
    private function generateVariantFields(array $row, DataContainer $dc): string
    {
        $attributes = [];

        foreach ($GLOBALS['TL_DCA'][$dc->table]['list']['label']['variantFields'] as $variantField) {
            $attributes[] = \sprintf(
                '<strong>%s:</strong>&nbsp;%s',
                $this->formatter->dcaLabel($dc->table, $variantField),
                $this->formatter->dcaValue($dc->table, $variantField, $row[$variantField] ?? null),
            );
        }

        return \sprintf('<div style="line-height: 1.3">%s</div>', implode('<br>', $attributes));
    }
}

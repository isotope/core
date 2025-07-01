<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Product;

use Codefog\HasteBundle\Formatter;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\Database;
use Contao\DataContainer;
use Contao\StringUtil;
use Isotope\CoreBundle\Model\ProductType;
use Isotope\Haste\Util\Format;
use Isotope\Model\Product;
use Isotope\Model\ProductPrice;
use Isotope\Model\TaxClass;

#[AsCallback('tl_iso_product', 'list.label.label')]
class LabelListener
{
    public function __construct(
        private readonly Studio $imageStudio,
        private readonly Formatter $formatter,
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
                    $args[$i] = $this->generateVariantFields($row, $args[$i], $dc);
                    break;

                default:
                    $objProductType = ProductType::findById($row['type']);
                    if (
                        $objProductType
                        && $objProductType->hasVariants()
                        && !\in_array($field, $objProductType->getAttributes(), true)
                        && \in_array($field, $objProductType->getVariantAttributes(), true)
                    ) {
                        $values = Database::getInstance()->prepare("SELECT $field FROM tl_iso_product WHERE pid=?")->execute($row['id'])->fetchEach($field);
                        $values = array_unique(array_filter($values));
                        $removed = array_splice($values, 3);

                        $args[$i] = implode('<br>', $values);

                        if ($removed) {
                            $args[$i] .= '<br><span title="'.StringUtil::specialchars(implode(', ', $removed)).'">…</span><span class="invisible">'.StringUtil::specialchars(implode(', ', $removed)).'</span>';
                        }
                    } else {
                        $args[$i] = $this->formatter->dcaValue('tl_iso_product', $field, $row[$field]);
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
        // Add a variants link
        if (
            0 === $row['pid']
            && null !== ($productType = ProductType::findById($row['type']))
            && $productType->hasVariants()
        ) {
            return \sprintf(
                '<a href="%s" title="%s">%s</a>',
                StringUtil::ampersand(Environment::get('request')).'&amp;id='.$row['id'],
                StringUtil::specialchars($GLOBALS['TL_LANG'][$dc->table]['showVariants']),
                $row['name'],
            );
        }

        return $row['name'];
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
            $variantIds = Database::getInstance()->prepare("SELECT id FROM tl_iso_product WHERE pid=? AND language=''")->execute($row['id'])->fetchEach('id');

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
                    return \sprintf($GLOBALS['TL_LANG']['MSC']['priceRangeLabel'], $objPrice->getValueForTier(1)).$strTax;
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
    private function generateVariantFields(array $row, string $label, DataContainer $dc): string
    {
        $objProduct = Product::findById($row['id']);

        if (!$objProduct) {
            return $label;
        }

        $attributes = [];

        foreach ($GLOBALS['TL_DCA'][$dc->table]['list']['label']['variantFields'] as $variantField) {
            $attributes[] = \sprintf(
                '<strong>%s:</strong>&nbsp;%s',
                Format::dcaLabel($dc->table, $variantField),
                Format::dcaValue($dc->table, $variantField, $objProduct->$variantField),
            );
        }

        return ($label ? $label.'<br>' : '').implode(', ', $attributes);
    }
}

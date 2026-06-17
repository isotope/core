<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\RelatedProduct;

use Codefog\HasteBundle\Formatter;
use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Util\StringUtil;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

#[AsCallback('tl_iso_related_product', 'list.label.label')]
class LabelListener
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly Connection $connection,
        private readonly Formatter $formatter,
        private readonly Environment $twig,
    ) {
    }

    public function __invoke(array $row, string $label, DataContainer $dc): array
    {
        $label = \sprintf(
            '<strong>%s:</strong> %s',
            $this->translator->trans('tl_iso_related_product.category.0', [], 'contao_tl_iso_related_product'),
            $label,
        );

        $products = StringUtil::csv($row['products']);

        if (!$products) {
            return [
                $label,
                '<div class="cte_type">'.$this->translator->trans('tl_iso_related_product.empty', [], 'contao_tl_iso_related_product').'</div>',
            ];
        }

        Controller::loadDataContainer('tl_iso_product');

        $fields = $GLOBALS['TL_DCA']['tl_iso_product']['list']['label']['fields'];
        $products = $this->connection->fetchAllAssociative(
            'SELECT * FROM tl_iso_product WHERE id IN (?) ORDER BY name',
            [$products],
            [ArrayParameterType::INTEGER],
        );

        $details = $this->twig->render('@Contao/backend/isotope/related-products.html.twig', [
            'table_headers' => array_combine($fields, array_map(fn ($field) => $this->formatter->dcaLabel('tl_iso_product', $field), $fields)),
            'records' => array_map(static fn ($product) => array_combine($fields, $dc->generateRecordLabel($product, 'tl_iso_product')), $products),
        ]);

        return [$label, $details];
    }
}

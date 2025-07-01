<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Product;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\Image;
use Contao\StringUtil;
use Isotope\CoreBundle\Controller\Backend\ShopConfigController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsCallback('tl_iso_product', 'fields.type.wizard')]
class ProductTypeWizard
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function __invoke(DataContainer $dc): string
    {
        if (!$dc->value) {
            return '';
        }

        $title = \sprintf($GLOBALS['TL_LANG']['tl_iso_producttype']['edit'][1], $dc->value);
        $href = $this->urlGenerator->generate(ShopConfigController::class, [
            'table' => 'tl_iso_producttype',
            'act' => 'edit',
            'id' => $dc->value,
            'popup' => '1',
            'nb' => '1',
        ]);

        return \sprintf(
            ' <a href="%s" style="padding-left:3px" onclick="Backend.openModalIframe({\'width\':768,\'title\':\'%s\',\'url\':this.href});return false">%s</a>',
            $href,
            StringUtil::specialchars(str_replace("'", "\\'", $title)),
            Image::getHtml('edit.svg', $title),
        );
    }
}

<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\Backend;

use Composer\InstalledVersions;
use Contao\CoreBundle\Controller\AbstractController;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\System;
use Isotope\CoreBundle\Backend\ShopConfig\ShopConfigInterface;
use Isotope\CoreBundle\Security\IsotopeCorePermissions;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ShopConfigController extends AbstractController
{
    /**
     * @param iterable<ShopConfigInterface> $shopConfigs
     */
    public function __construct(
        private readonly Packages $assets,
        #[AutowireIterator('isotope.shop_config')] private readonly iterable $shopConfigs,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted(IsotopeCorePermissions::USER_CAN_ACCESS_SETUP);
        $this->initializeContaoFramework();
        System::loadLanguageFile('default');

        $GLOBALS['TL_CSS'][] = $this->assets->getUrl('backend.css', 'isotope');
        $GLOBALS['TL_JAVASCRIPT'][] = $this->assets->getUrl('backend.js', 'isotope');

        $links = [];

        foreach ($this->shopConfigs as $config) {
            if (!$this->isGranted(IsotopeCorePermissions::USER_CAN_ACCESS_MODULE, $config->getName())) {
                continue;
            }

            $url = $config->getUrl();

            if (
                $url
                && [] !== $config->getTables()
                && !$this->isGranted(ContaoCorePermissions::USER_CAN_EDIT_FIELDS_OF_TABLE, $config->getTables()[0])
            ) {
                $url = null;
            }

            $links[$config->getGroup()][] = [
                'name' => $config->getName(),
                'url' => $url,
                'icon' => $config->getIcon(),
            ];
        }

        return $this->render('@Contao/backend/isotope/shop-config.html.twig', [
            'version' => InstalledVersions::getPrettyVersion('isotope/isotope-core'),
            'links' => $links,
        ]);
    }
}

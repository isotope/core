<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\Backend;

use Composer\InstalledVersions;
use Contao\BackendMain;
use Contao\Controller;
use Contao\CoreBundle\Controller\AbstractBackendController;
use Contao\CoreBundle\Event\MenuEvent;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\Input;
use Contao\System;
use Isotope\CoreBundle\Backend\ShopConfig\ShopConfigInterface;
use Isotope\CoreBundle\Security\IsotopeCorePermissions;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('%contao.backend.route_prefix%/isotope-config', defaults: ['_scope' => 'backend'])]
class ShopConfigController extends AbstractBackendController
{
    /**
     * @param iterable<ShopConfigInterface> $shopConfigs
     */
    public function __construct(
        #[AutowireIterator('isotope.shop_config')] private readonly iterable $shopConfigs,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted(IsotopeCorePermissions::USER_CAN_ACCESS_SETUP);
        $this->initializeContaoFramework();
        System::loadLanguageFile('default');
        $this->registerTables();

        if ($request->query->has('table')) {
            return $this->renderTable($request->query->get('table'));
        }

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

    #[AsEventListener]
    public function renderMenu(MenuEvent $event): void
    {
        $tree = $event->getTree();

        if ('mainMenu' !== $tree->getName()) {
            return;
        }

        $tree
            ->getChild('isotope')
            ?->getChild('iso_setup')
            ?->setUri($this->getOverviewUrl())
        ;
    }

    private function registerTables(): void
    {
        $tables = [];

        foreach ($this->shopConfigs as $config) {
            $tables[] = $config->getTables();
        }

        $GLOBALS['BE_MOD']['isotope']['iso_setup'] = [
            'tables' => array_merge(...$tables),
        ];
    }

    private function renderTable(string $table): Response
    {
        $config = $this->findConfig($table);

        $this->denyAccessUnlessGranted(IsotopeCorePermissions::USER_CAN_ACCESS_MODULE, $config->getName());
        $this->denyAccessUnlessGranted(ContaoCorePermissions::USER_CAN_EDIT_FIELDS_OF_TABLE, $table);

        Controller::loadDataContainer($table);

        $GLOBALS['TL_DCA'][$table]['config']['backlink'] = $this->getOverviewUrl();

        // Set module in URL so BackendMain knows what to render
        Input::setGet('do', 'iso_setup');

        return (new BackendMain())->run();
    }

    private function findConfig(string $table): ShopConfigInterface
    {
        foreach ($this->shopConfigs as $config) {
            if (\in_array($table, $config->getTables(), true)) {
                return $config;
            }
        }

        throw new \UnderflowException('No shop config for table "'.$table.'" found.');
    }

    private function getOverviewUrl(): string
    {
        return $this->generateUrl(self::class);
    }
}

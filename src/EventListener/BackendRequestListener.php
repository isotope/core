<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener;

use Contao\Controller;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\System;
use Isotope\CoreBundle\Backend\ShopConfig\ShopConfigInterface;
use Isotope\CoreBundle\Controller\Backend\ShopConfigController;
use Isotope\CoreBundle\Security\IsotopeCorePermissions;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsEventListener]
class BackendRequestListener
{
    /**
     * @param iterable<ShopConfigInterface> $shopConfigs
     */
    public function __construct(
        private readonly ScopeMatcher $scopeMatcher,
        private readonly Packages $packages,
        private readonly Security $security,
        private readonly UrlGeneratorInterface $urlGenerator,
        #[AutowireIterator('isotope.shop_config')] private readonly iterable $shopConfigs,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        if (!$this->scopeMatcher->isBackendMainRequest($event)) {
            return;
        }

        $GLOBALS['TL_JAVASCRIPT'][] = $this->packages->getUrl('backend.js', 'isotope_core');
        $GLOBALS['TL_CSS'][] = $this->packages->getUrl('backend.css', 'isotope_core');

        $this->handleShopConfig($event->getRequest());
    }

    public function handleShopConfig(Request $request): void
    {
        $tables = [];

        foreach ($this->shopConfigs as $config) {
            $tables[] = $config->getTables();
        }

        $GLOBALS['BE_MOD']['isotope']['iso_setup'] = [
            'callback' => ShopConfigCallback::class,
        ];

        if ('iso_setup' !== $request->query->get('do') || !$request->query->has('table')) {
            return;
        }

        $GLOBALS['BE_MOD']['isotope']['iso_setup'] = [
            'tables' => array_merge(...$tables),
        ];

        $table = $request->query->get('table');
        $config = $this->findConfig($table);

        if (
            !$this->security->isGranted(IsotopeCorePermissions::USER_CAN_ACCESS_MODULE, $config->getName())
            || !$this->security->isGranted(ContaoCorePermissions::USER_CAN_EDIT_FIELDS_OF_TABLE, $table)
        ) {
            throw new AccessDeniedException();
        }

        Controller::loadDataContainer($table);

        if ($config->getTables()[0] === $table) {
            $GLOBALS['TL_DCA'][$table]['config']['backlink'] = $this->urlGenerator->generate('contao_backend', ['do' => 'iso_setup']);
        }
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
}

class ShopConfigCallback
{
    public function generate(): string
    {
        /** @var ShopConfigController $controller */
        $controller = System::getContainer()->get(ShopConfigController::class);

        return $controller->__invoke(
            System::getContainer()->get('request_stack')->getCurrentRequest(),
        )->getContent();
    }
}

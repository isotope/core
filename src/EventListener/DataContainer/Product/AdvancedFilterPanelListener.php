<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Product;

use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Picker\PickerBuilderInterface;
use Contao\DataContainer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class AdvancedFilterPanelListener
{
    public function __construct(
        private readonly Environment $twig,
        private readonly RequestStack $requestStack,
        private readonly PickerBuilderInterface $pickerBuilder,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[AsCallback('tl_iso_product', 'list.sorting.panel_callback.iso_filter', exact: true, singleton: true)]
    public function generateFilters(): string
    {
        // Do not show on product variant view
        if ($this->requestStack->getCurrentRequest()?->query->get('id') > 0) {
            return '';
        }

        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = $this->requestStack->getSession()->getBag('contao_backend');
        $session = $sessionBag->all();

        return $this->twig->render('@Contao/backend/isotope/advanced-product-filters.html.twig', [
            'pagePickerUrl' => $this->pickerBuilder->getUrl('dc.tl_page', ['fieldType' => 'radio']),
            'pageValue' => (int) ($session['filter']['tl_iso_product']['iso_page'] ?? 0),
            'filters' => [
                [
                    'name' => 'iso_noimages',
                    'label' => $this->translator->trans('tl_iso_product.filter_noimages', [], 'contao_tl_iso_product'),
                    'value' => $session['filter']['tl_iso_product']['iso_noimages'] ?? null,
                    'options' => [
                        ['value' => '', 'label' => $this->translator->trans('MSC.no', [], 'contao_tl_iso_product')],
                        ['value' => '1', 'label' => $this->translator->trans('MSC.yes', [], 'contao_tl_iso_product')],
                    ],
                ],
                [
                    'name' => 'iso_nocategory',
                    'label' => $this->translator->trans('tl_iso_product.filter_nocategory', [], 'contao_tl_iso_product'),
                    'value' => $session['filter']['tl_iso_product']['iso_nocategory'] ?? null,
                    'options' => [
                        ['value' => '', 'label' => $this->translator->trans('MSC.no', [], 'contao_tl_iso_product'), 'selected' => false],
                        ['value' => '1', 'label' => $this->translator->trans('MSC.yes', [], 'contao_tl_iso_product'), 'selected' => false],
                    ],
                ],
            ],
        ]);
    }

    #[AsCallback('tl_iso_product', 'config.onload')]
    public function applyFilters(DataContainer $dc): void
    {
        // Do not run on product variant view
        if ($this->requestStack->getCurrentRequest()?->query->get('id') > 0) {
            return;
        }

        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = $this->requestStack->getSession()->getBag('contao_backend');
        $session = $sessionBag->all();
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return;
        }

        $this->storeFilters($request, $sessionBag);

        if (!isset($session['filter']['tl_iso_product'])) {
            return;
        }

        // Show products with or without images
        if (isset($session['filter']['tl_iso_product']['iso_noimages'])) {
            $yes = (bool) $session['filter']['tl_iso_product']['iso_noimages'];
            $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['filter'][] = ['language=? AND images IS '.($yes ? 'NULL' : 'NOT NULL'), ''];
            $dc->setPanelState(true);
        }

        // Show products with or without category
        if (isset($session['filter']['tl_iso_product']['iso_nocategory'])) {
            $yes = (bool) $session['filter']['tl_iso_product']['iso_nocategory'];
            $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['filter'][] = ['language=? AND (SELECT COUNT(*) FROM tl_iso_product_category c WHERE c.product_id=tl_iso_product.id) '.($yes ? '=0' : '>0'), ''];
            $dc->setPanelState(true);
        }

        // Filter the products by pages
        if (($session['filter']['tl_iso_product']['iso_page'] ?? 0) > 0) {
            $pageId = (int) $session['filter']['tl_iso_product']['iso_page'];
            $GLOBALS['TL_DCA']['tl_iso_product']['list']['sorting']['filter'][] = ["pid=0 AND language='' AND id IN (SELECT product_id FROM tl_iso_product_category WHERE pid=?)", $pageId];
            $dc->setPanelState(true);
        }
    }

    #[AsHook('executePreActions')]
    public function storePage(string $action): void
    {
        if ('filterPages' !== $action) {
            return;
        }

        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = $this->requestStack->getSession()->getBag('contao_backend');
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return;
        }

        $filter = $sessionBag->get('filter');
        $filter['tl_iso_product']['iso_page'] = $request->request->getInt('value');
        $sessionBag->set('filter', $filter);

        // Cannot use RedirectResponseException because this handles special Ajax redirects
        Controller::reload();
    }

    private function storeFilters(Request $request, AttributeBagInterface $sessionBag): void
    {
        if (!$request->isMethod(Request::METHOD_POST) || 'tl_filters' !== $request->request->get('FORM_SUBMIT')) {
            return;
        }

        $filter = $sessionBag->get('filter', []);

        foreach (['iso_noimages', 'iso_nocategory'] as $key) {
            $value = $request->request->get($key);

            // Reset the filter if value equals name
            if ($key === $value) {
                unset($filter['tl_iso_product'][$key]);
                continue;
            }

            $filter['tl_iso_product'][$key] = $value;
        }

        $sessionBag->set('filter', $filter);
    }
}

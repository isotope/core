<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\EventListener;

use Haste\Input\Input;
use Isotope\Model\Product;
use Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent;

class ChangeLanguageListener
{
    /**
     * Hook callback for ChangeLangauge v3
     *
     * @param ChangelanguageNavigationEvent $event
     */
    public function __invoke(ChangelanguageNavigationEvent $event)
    {
        if (($uid = $this->getUid()) !== null) {
            $event->getUrlParameterBag()->setQueryParameter('uid', $uid);
        } else if (($step = $this->getCheckoutStep()) !== null) {
            $event->getUrlParameterBag()->setUrlAttribute('step', $step);
        } else if (($product = $this->getProductAlias()) !== null) {
            $event->getUrlParameterBag()->setUrlAttribute('product', $product);
        }
    }

    /**
     * @return null|string
     */
    private function getUid()
    {
        $uid = (string) Input::get('uid', false, true);

        return '' === $uid ? null : $uid;
    }

    /**
     * @return null|string
     */
    private function getCheckoutStep()
    {
        if (!\is_array($GLOBALS['ISO_CHECKOUT_STEPS'] ?? null)) {
            return null;
        }

        $step = (string) Input::getAutoItem('step', false, true);

        return ('' !== $step && \array_key_exists($step, $GLOBALS['ISO_CHECKOUT_STEPS'])) ? $step : null;
    }

    /**
     * @return null|string
     */
    private function getProductAlias()
    {
        $alias = (string) Input::getAutoItem('product', false, true);

        return '' !== $alias && null !== Product::findAvailableByIdOrAlias($alias) ? $alias : null;
    }
}

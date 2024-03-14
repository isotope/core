<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Frontend\ProductCollectionAction;

use Contao\Controller;
use Isotope\Interfaces\IsotopeProductCollection;

class LegacyButtonAction extends AbstractButton
{
    /**
     * @var array
     */
    private $button;

    /**
     * Constructor.
     */
    public function __construct(array $button)
    {
        $this->button = $button;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->button['name'];
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(IsotopeProductCollection $collection)
    {
        return $this->button['label'];
    }

    /**
     * @inheritDoc
     */
    public function handleSubmit(IsotopeProductCollection $collection)
    {
        if (!parent::handleSubmit($collection)) {
            return false;
        }

        if (\is_string($this->button['action'])) {
            Controller::redirect($this->button['action']);
        }

        \call_user_func(
            $this->button['action'],
            array_merge(
                [
                    'type'      => 'submit',
                    'name'      => 'button_' . $this->button['name'],
                    'label'     => $this->button['label'],
                ],
                $this->button['additional']
            )
        );

        return true;
    }


    /**
     * @return string|null
     */
    public function getHref()
    {
        return \is_string($this->button['action']) ? $this->button['action'] : null;
    }
}

<?php

namespace Isotope\Frontend\ProductCollectionAction;

use Contao\Module;
use Isotope\Interfaces\IsotopeProductCollection;

class GoToCartAction extends AbstractButton
{
    /**
     * @var \Module
     */
    private $module;

    /**
     * Constructor.
     *
     * @param Module $module
     */
    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable(IsotopeProductCollection $collection)
    {
        return $this->module->iso_cart_jumpTo > 0 && \PageModel::findByPk($this->module->iso_cart_jumpTo) !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'cart';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(IsotopeProductCollection $collection)
    {
        return $GLOBALS['TL_LANG']['MSC']['cartBT'];
    }

    /**
     * {@inheritdoc}
     */
    public function handleSubmit(IsotopeProductCollection $collection)
    {
        if (!parent::handleSubmit($collection)) {
            return false;
        }

        \Controller::redirect($this->getHref());

        return true;
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return \PageModel::findByPk($this->module->iso_cart_jumpTo)->getFrontendUrl();
    }
}

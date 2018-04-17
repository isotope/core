<?php

namespace Isotope\Frontend\ProductCollectionAction;

use Contao\Module;
use Isotope\Interfaces\IsotopeProductCollection;

class GoToCheckoutAction extends AbstractButton
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
        return $this->module->iso_checkout_jumpTo > 0
            && !$collection->hasErrors()
            && \PageModel::findByPk($this->module->iso_checkout_jumpTo) !== null
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'checkout';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(IsotopeProductCollection $collection)
    {
        return $GLOBALS['TL_LANG']['MSC']['checkoutBT'];
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
        return \PageModel::findByPk($this->module->iso_checkout_jumpTo)->getFrontendUrl();
    }
}

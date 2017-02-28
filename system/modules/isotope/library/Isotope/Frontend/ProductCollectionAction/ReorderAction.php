<?php

namespace Isotope\Frontend\ProductCollectionAction;

use Contao\Module;
use Haste\Util\Url;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Message;

class ReorderAction extends AbstractButton
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
        return $this->module->iso_cart_jumpTo > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'reorder';
    }

    /**
     * {@inheritdoc}
     */
    public function handleSubmit(IsotopeProductCollection $collection)
    {
        if (!parent::handleSubmit($collection)) {
            return false;
        }

        Isotope::getCart()->copyItemsFrom($collection);

        Message::addConfirmation($GLOBALS['TL_LANG']['MSC']['reorderConfirmation']);

        \Controller::redirect(
            Url::addQueryString(
                'continue=' . base64_encode(\System::getReferer()),
                $this->module->iso_cart_jumpTo
            )
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(IsotopeProductCollection $collection)
    {
        return $GLOBALS['TL_LANG']['MSC']['reorderLabel'];
    }
}

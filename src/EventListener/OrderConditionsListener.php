<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener;

use Codefog\HasteBundle\Form\Form;
use Contao\StringUtil;
use Contao\UploadableWidgetInterface;
use Isotope\CoreBundle\Checkout\Event\CheckoutStepEvent;
use Isotope\CoreBundle\ProductCollection\Extension\OrderConditionsExtension;
use Isotope\CoreBundle\Twig\TemplateMarkup;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class OrderConditionsListener
{
    public function __construct(
        private readonly TemplateMarkupFactory $markupFactory,
    ) {
    }

    public function __invoke(CheckoutStepEvent $event): void
    {
        $conditions = StringUtil::deserialize($event->getConfig()['iso_order_conditions'] ?? [], true);

        foreach ($conditions as $condition) {
            if (
                $event->getType() !== ($condition['step'] ?? null)
                || empty($condition['form'])
            ) {
                continue;
            }

            if ($markup = $this->getForm($event, $condition)) {
                switch ($condition['position'] ?? null) {
                    case 'before':
                        $event->addPreMarkup($markup);
                        break;

                    case 'after':
                        $event->addPostMarkup($markup);
                        break;
                }
            }
        }
    }

    /**
     * @param array{form: int} $condition
     */
    private function getForm(CheckoutStepEvent $event, array $condition): TemplateMarkup|null
    {
        $form = new Form($event->getFormId(), 'POST', fn () => $event->isSubmitted());
        $cart = $event->getContext()->getCart();
        $extension = $cart?->getExtension(OrderConditionsExtension::class);

        if (!$extension) {
            $extension = new OrderConditionsExtension();
            $cart?->addExtension($extension);
        }

        try {
            $form->addFieldsFromFormGenerator(
                (int) $condition['form'],
                function ($strName, &$arrDca) use ($event, $extension) {
                    $arrDca['form'] = $event->getFormId();
                    $arrDca['value'] = $extension->get($strName) ?? $arrDca['value'] ?? null;

                    return true;
                }
            );
        } catch (\InvalidArgumentException) {
            return null;
        }

        if (!$form->hasFormFields()) {
            return null;
        }

        // Change enctype if there are uploads
        if ($form->hasUploads()) {
            $event->setHasUploads();
        }

        $hasError = false;

        if ($form->isSubmitted()) {
            if (!$form->validate()) {
                $hasError = true;
            }

            foreach (array_keys($form->getFormFields()) as $strField) {
                if ($form->getWidget($strField) instanceof UploadableWidgetInterface) {
                    if (isset($_SESSION['FILES'][$strField])) {
                        $arrFile = $_SESSION['FILES'][$strField];
                        $varValue = str_replace(TL_ROOT . '/', '', \dirname($arrFile['tmp_name'])) . '/' . rawurlencode($arrFile['name']);
                    } else {
                        $varValue = null;
                    }
                } else {
                    $varValue = $form->fetch($strField);
                }

                $extension->set($strField, $varValue);
            }

        } else {
            foreach (array_keys($form->getFormFields()) as $strField) {

                // Clone widget because otherwise we add errors to the original widget instance
                $objClone = clone $form->getWidget($strField);
                if ($objClone instanceof UploadableWidgetInterface) {
                    $_FILES[$strField] = $_SESSION['FILES'][$strField] ?? null;
                } else {
                    $objClone->setInputCallback(fn () => $extension->get($strField));
                }

                $objClone->validate();

                if ($objClone->hasErrors()) {
                    $hasError = true;
                    break;
                }
            }
        }

        if ($hasError) {
            $event->setHasError();
        }

        if ($hasError || $event->isEditing()) {
            return $this->markupFactory->create('@Contao/isotope/checkout_step/order_condition.html.twig', (array) $form->getHelperObject());
        }

        return null;
    }
}

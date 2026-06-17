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
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class OrderConditionsListener
{
    public function __construct(
        private readonly TemplateMarkupFactory $markupFactory,
        #[Autowire(param: 'kernel.project_dir')] private readonly string $projectDir,
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
        $form = new Form($event->getFormId(), 'POST', static fn () => $event->isSubmitted());
        $cart = $event->getContext()->getCart();
        $extension = $cart?->getExtension(OrderConditionsExtension::class);

        if (!$extension) {
            $extension = new OrderConditionsExtension();
            $cart?->addExtension($extension);
        }

        try {
            $form->addFieldsFromFormGenerator(
                (int) $condition['form'],
                static function ($name, &$dca) use ($event, $extension) {
                    $dca['form'] = $event->getFormId();
                    $dca['value'] = $extension->get($name) ?? $dca['value'] ?? null;

                    return true;
                },
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

            foreach (array_keys($form->getFormFields()) as $field) {
                if ($form->getWidget($field) instanceof UploadableWidgetInterface) {
                    if (isset($_SESSION['FILES'][$field])) {
                        $file = $_SESSION['FILES'][$field];
                        $value = str_replace($this->projectDir.'/', '', \dirname((string) $file['tmp_name'])).'/'.rawurlencode((string) $file['name']);
                    } else {
                        $value = null;
                    }
                } else {
                    $value = $form->fetch($field);
                }

                $extension->set($field, $value);
            }
        } else {
            foreach (array_keys($form->getFormFields()) as $field) {
                // Clone widget because otherwise we add errors to the original widget instance
                $clone = clone $form->getWidget($field);
                if ($clone instanceof UploadableWidgetInterface) {
                    $_FILES[$field] = $_SESSION['FILES'][$field] ?? null;
                } else {
                    $clone->setInputCallback(static fn () => $extension->get($field));
                }

                $clone->validate();

                if ($clone->hasErrors()) {
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

<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Address;

use Codefog\HasteBundle\Form\Form;
use Contao\CoreBundle\Intl\Countries;
use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\Twig\TemplateMarkup;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;
use Symfony\Component\HttpFoundation\Request;

class FormBuilder
{
    public function __construct(
        private readonly AddressValidator $addressValidator,
        private readonly Countries $countries,
        private readonly TemplateMarkupFactory $markupFactory,
    ) {
    }

    public function build(Request $request, AddressModel $address, AddressConfig $addressConfig, string $prefix = ''): Form
    {
        $formId = 'iso_'.$prefix.'address';

        $form = new Form($formId, 'POST', fn () => $request->isMethod(Request::METHOD_POST) && $request->request->has($formId));
        $form->setBoundModel($address);
        $form->addFieldsFromDca('tl_iso_address', fn (string &$field, array &$config) => $this->addDcaField($field, $config, $addressConfig, $prefix));

        $form->addFormField($formId, [
            'inputType' => 'hidden',
            'ignoreModelValue' => true,
            'value' => $address?->id ?? '1',
        ]);

        return $form;
    }

    public function validate(Form $form, AddressConfig $addressConfig): array|bool
    {
        $form->validate();

        if (!$form->isSubmitted() || !$form->isValid()) {
            return false;
        }

        $address = $form->getBoundModel();

        if (!$address instanceof AddressModel) {
            return false;
        }

        $violations = $this->addressValidator->validate($address, $addressConfig);

        if ($violations->count()) {
            $errors = [];

            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }

            return $errors;
        }

        return true;
    }

    public function render(Form $form, array $errors = []): TemplateMarkup
    {
        $templateObject = $form->getHelperObject();
        $templateObject->class = '';
        $templateObject->errors = $errors;
        $templateObject->formSubmit = $templateObject->formId;

        return $this->markupFactory->create('@Contao/form_inline.html.twig', (array) $templateObject);
    }

    private function addDcaField(string &$field, array &$config, AddressConfig $addressConfig, string $prefix): bool
    {
        if (!($config['eval']['feEditable'] ?? false) || !\in_array($field, $addressConfig->getFields(), true)) {
            return false;
        }

        $config['eval']['mandatory'] = $addressConfig->isMandatory($field);

        if ('country' === $field && [] !== $addressConfig->getCountryCodes()) {
            $config['options'] = array_intersect_key($this->countries->getCountries(), array_flip($addressConfig->getCountryCodes()));
            unset($config['options_callback']);
        }

        if (isset($config['eval']['conditionField'])) {
            // Special field type "conditionalselect"
            $config['eval']['conditionField'] = $prefix.$config['eval']['conditionField'];
        }

        $config['boundName'] = $field;
        $field = $prefix.$field;

        return true;
    }
}

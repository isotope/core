<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Widget;

use Contao\CheckBox;
use Contao\Input;
use Contao\System;

/**
 * Provide methods to inherit checkbox fields.
 */
class InheritCheckBox extends CheckBox
{
    /**
     * Disable mandatory validation for inherited attributes.
     */
    #[\Override]
    public function validate(): void
    {
        parent::validate();

        if (!empty($this->varValue) && \is_array($this->varValue)) {
            foreach ($this->varValue as $field) {
                $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$field]['eval']['mandatory'] = false;
            }
        }
    }

    #[\Override]
    public function generate(): string
    {
        $options = [];

        foreach ($this->arrOptions as $option) {
            $options[] = [
                ...$option,
                'checked' => $this->isChecked($option),
            ];
        }

        return System::getContainer()->get('twig')->render('@Contao/backend/iso_inherit_checkbox.html.twig', [
            'id' => $this->strId,
            'class' => $this->strClass,
            'label' => $this->strLabel,
            'xlabel' => $this->xlabel,
            'name' => $this->strName,
            'mandatory' => $this->mandatory,
            'inherit' => 'edit' === Input::get('act'),
            'options' => $options,
        ]);
    }
}

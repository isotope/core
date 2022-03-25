<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Widget;


use Contao\Input;
use Contao\StringUtil;

/**
 * Provide methods to inherit checkbox fields.
 */
class InheritCheckBox extends \CheckBox
{

    /**
     * Disable mandatory validation for inherited attributes
     */
    public function validate()
    {
        parent::validate();

        if (\is_array($this->varValue) && !empty($this->varValue)) {
            foreach ($this->varValue as $field) {
                $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$field]['eval']['mandatory'] = false;
            }
        }
    }


    /**
     * Generate the widget and return it as string
     * @return string
     */
    public function generate()
    {
        $strJS = '';
        $blnCheckAll = true;
        $arrFields  = array();
        $arrOptions = array();

        foreach ($this->arrOptions as $i => $arrOption) {
            $arrFields[]  = $arrOption['value'];
            $arrOptions[] = $this->generateCheckbox($arrOption, $i);
        }

        // Add a "no entries found" message if there are no options
        if (empty($arrOptions)) {
            $arrOptions[] = '<p class="tl_noopt">' . $GLOBALS['TL_LANG']['MSC']['noResult'] . '</p>';
            $blnCheckAll = false;
        }

        if ('edit' === Input::get('act')) {
            $strJS = "
<script>
window.addEvent('domready', function() {
  Isotope.inheritFields(['" . implode("','", $arrFields) . "'], '" . str_replace("'", "\'", $GLOBALS['TL_LANG']['MSC']['useDefault']) . "');
});
</script>
";
        }

        return sprintf('<fieldset id="ctrl_%s" class="tl_checkbox_container%s"><legend>%s%s%s%s</legend><input type="hidden" name="%s" value="">%s%s</fieldset>%s%s',
            $this->strId,
            (($this->strClass != '') ? ' ' . $this->strClass : ''),
            ($this->mandatory ? '<span class="invisible">'.$GLOBALS['TL_LANG']['MSC']['mandatory'].'</span> ' : ''),
            $this->strLabel,
            ($this->mandatory ? '<span class="mandatory">*</span>' : ''),
            $this->xlabel,
            $this->strName,
            ($blnCheckAll ? '<input type="checkbox" id="check_all_' . $this->strId . '" class="tl_checkbox" onclick="Backend.toggleCheckboxGroup(this,\'ctrl_' . $this->strId . '\')' . ($this->onclick ? ';' . $this->onclick : '') . '"> <label for="check_all_' . $this->strId . '" style="color:#a6a6a6"><em>' . $GLOBALS['TL_LANG']['MSC']['selectAll'] . '</em></label><br>' : ''),
            str_replace('<br></div><br>', '</div>', implode('<br>', $arrOptions)),
            $this->wizard,
            $strJS
        );
    }


    /**
     * Generate a checkbox and return it as string
     * @param array
     * @param integer
     * @return string
     */
    protected function generateCheckbox($arrOption, $i)
    {
        return sprintf('<input type="checkbox" name="%s" id="opt_%s" class="tl_checkbox" value="%s"%s%s onfocus="Backend.getScrollOffset();"> <label for="opt_%s">%s</label>',
            $this->strName . '[]',
            $this->strId . '_' . $i,
            StringUtil::specialchars($arrOption['value'], true),
            $this->isChecked($arrOption),
            $this->getAttributes(),
            $this->strId . '_' . $i,
            $arrOption['label']);
    }
}

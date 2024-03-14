<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Product;

use Contao\Backend;
use Contao\CheckBox;
use Contao\Controller;
use Contao\Database;
use Contao\DataContainer;
use Contao\Environment;
use Contao\Input;
use Contao\Message;
use Contao\StringUtil;
use Isotope\Model\Attribute;
use Isotope\Model\Product;

class VariantGenerator extends Backend
{

    /**
     * Generate all combination of product attributes
     *
     * @param DataContainer $dc
     *
     * @return string
     */
    public function generate($dc)
    {
        $objProduct = Product::findByPk($dc->id);

        $doNotSubmit = false;
        $strBuffer = '';
        $values = [];

        foreach ($objProduct->getType()->getVariantAttributes() as $attribute) {
            if ($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$attribute]['attributes']['variant_option'] ?? false) {
                $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$attribute]['eval']['mandatory'] = true;
                $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$attribute]['eval']['multiple']  = true;

                $arrField = CheckBox::getAttributesFromDca(
                    $GLOBALS['TL_DCA']['tl_iso_product']['fields'][$attribute],
                    $attribute,
                    null,
                    $attribute,
                    'tl_iso_product',
                    $dc
                );

                foreach ($arrField['options'] as $k => $option) {
                    if (empty($option['value'])) {
                        unset($arrField['options'][$k]);
                    }
                }

                $objWidget = new CheckBox($arrField);

                if ('tl_iso_product_generate' === Input::post('FORM_SUBMIT')) {
                    $objWidget->validate();

                    if ($objWidget->hasErrors()) {
                        $doNotSubmit = true;
                    } else {
                        $values[$attribute] = $objWidget->value;
                    }
                }

                $strBuffer .= '<div class="clr widget">'.$objWidget->parse().'</div>';
            }
        }

        if (!$doNotSubmit && 'tl_iso_product_generate' === Input::post('FORM_SUBMIT')) {
            $this->handle($objProduct, $values);
        }

        return '
<div id="tl_buttons">
<a href="' . \Contao\StringUtil::ampersand(str_replace('&key=generate', '', Environment::get('request'))) . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>

<h2 class="sub_headline">' . sprintf($GLOBALS['TL_LANG']['tl_iso_product']['generate'][1], $dc->id) . '</h2>' . Message::generate() . '

<form id="tl_iso_product_generate" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_iso_product_generate">
<input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">

<fieldset class="tl_tbox block">
' . $strBuffer . '
</fieldset>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  <input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="' . StringUtil::specialchars($GLOBALS['TL_LANG']['tl_iso_product']['generate'][0]) . '">
</div>

</div>
</form>';
    }

    public function handle(Product $objProduct, array $values)
    {
        $time = time();
        $arrCombinations = [];

        foreach ($values as $name => $options) {
            $arrTemp = $arrCombinations;
            $arrCombinations = [];

            foreach ($options as $option) {
                if (empty($arrTemp)) {
                    $arrCombinations[][$name] = $option;
                    continue;
                }

                foreach ($arrTemp as $temp) {
                    $temp[$name] = $option;
                    $arrCombinations[] = $temp;
                }
            }
        }

        foreach ($arrCombinations as $combination) {
            $objVariant = Database::getInstance()->prepare('
                    SELECT * FROM tl_iso_product WHERE pid=? AND ' . implode('=? AND ', array_keys($combination)) . '=?'
            )->execute(array_merge([$objProduct->id], $combination));

            if (!$objVariant->numRows) {
                $arrInherit = array_diff(
                    $objProduct->getType()->getVariantAttributes(),
                    Attribute::getVariantOptionFields(),
                    Attribute::getCustomerDefinedFields(),
                    Attribute::getSystemColumnsFields()
                );

                $arrSet = array_merge(
                    $combination,
                    [
                        'tstamp' => $time,
                        'pid' => $objProduct->id,
                        'inherit' => $arrInherit ?: null,
                    ]
                );

                Database::getInstance()->prepare("INSERT INTO tl_iso_product %s")->set($arrSet)->execute();
            }
        }

        Controller::redirect(str_replace('&key=generate', '', Environment::get('request')));
    }
}

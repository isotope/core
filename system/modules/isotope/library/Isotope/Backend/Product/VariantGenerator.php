<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 * @author     Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 */

namespace Isotope\Backend\Product;

use Isotope\Model\Product;


class VariantGenerator extends \Backend
{

    /**
     * Generate all combination of product attributes
     * @param object
     * @return string
     */
    public function generate($dc)
    {
        $table = Product::getTable();
        $objProduct = Product::findByPk($dc->id);

        $doNotSubmit = false;
        $strBuffer = '';
        $arrOptions = array();

        foreach ($objProduct->getRelated('type')->getVariantAttributes() as $attribute) {
            if ($GLOBALS['TL_DCA'][$table]['fields'][$attribute]['attributes']['variant_option']) {

                $GLOBALS['TL_DCA'][$table]['fields'][$attribute]['eval']['mandatory'] = true;
                $GLOBALS['TL_DCA'][$table]['fields'][$attribute]['eval']['multiple'] = true;

                $arrField = \CheckBox::getAttributesFromDca($GLOBALS['TL_DCA'][$table]['fields'][$attribute], $attribute);

                foreach ($arrField['options'] as $k => $option) {
                    if ($option['value'] == '') {
                        unset($arrField['options'][$k]);
                    }
                }

                $objWidget = new \CheckBox($arrField);

                if (\Input::post('FORM_SUBMIT') == ($table.'_generate')) {
                    $objWidget->validate();

                    if ($objWidget->hasErrors()) {
                        $doNotSubmit = true;
                    } else {
                        $arrOptions[$attribute] = $objWidget->value;
                    }
                }

                $strBuffer .= $objWidget->parse();
            }
        }

        if (\Input::post('FORM_SUBMIT') == $table.'_generate' && !$doNotSubmit) {
            $time = time();
            $arrCombinations = array();

            foreach ($arrOptions as $name => $options) {
                $arrTemp = $arrCombinations;
                $arrCombinations = array();

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

                $objVariant = \Database::getInstance()->prepare("
                    SELECT * FROM $table WHERE pid=? AND " . implode('=? AND ', array_keys($combination)) . "=?"
                )->execute(array_merge(array($objProduct->id), $combination));

                if (!$objVariant->numRows) {

                    $arrSet = array_merge($combination, array(
                        'tstamp'    => $time,
                        'pid'       => $objProduct->id,
                        'inherit'   => array_diff($objProduct->getRelated('type')->getVariantAttributes(), Attribute::getSystemColumnsFields()),
                    ));

                    \Database::getInstance()->prepare("INSERT INTO $table %s")->set($arrSet)->execute();
                }
            }

            \Controller::redirect(str_replace('&key=generate', '', \Environment::get('request')));
        }

        // Return form
        return '
<div id="tl_buttons">
<a href="'.ampersand(str_replace('&key=generate', '', \Environment::get('request'))).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.sprintf($GLOBALS['TL_LANG'][$table]['generate'][1], $dc->id).'</h2>'.\Message::generate().'

<form action="'.ampersand(\Environment::get('request'), true).'" id="'.$table.'_generate" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="'.$table.'_generate">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">

<div class="tl_tbox block">
' . $strBuffer . '
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  <input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['tl_iso_product']['generate'][0]).'">
</div>

</div>
</form>';
    }
}

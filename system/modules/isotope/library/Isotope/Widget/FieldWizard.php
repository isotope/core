<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Widget;


/**
 * Class FieldWizard
 *
 * Provide methods to handle fields table.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 * @author     Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 */
class FieldWizard extends \Widget
{

    /**
     * Submit user input
     * @var boolean
     */
    protected $blnSubmitInput = true;

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'be_widget';

    /**
     * Options
     * @var array
     */
    protected $arrOptions = array();


    /**
     * Add specific attributes
     * @param string
     * @param mixed
     */
    public function __set($strKey, $varValue)
    {
        switch ($strKey)
        {
            case 'value':
                $this->varValue = deserialize($varValue);
                break;

            case 'options':
                break;

            case 'table':
                \System::loadLanguageFile($varValue);
                $this->loadDataContainer($varValue);

                $this->arrOptions = array();

                foreach ($GLOBALS['TL_DCA'][$varValue]['fields'] as $name => $arrData)
                {
                    if ($arrData['eval']['feEditable'])
                    {
                        $this->arrOptions[] = $name;
                    }
                }

                parent::__set($strKey, $varValue);
                break;

            case 'mandatory':
                $this->arrConfiguration['mandatory'] = $varValue ? true : false;
                break;

            case 'maxlength':
                $this->arrAttributes[$strKey] = ($varValue > 0) ? $varValue : '';
                break;

            default:
                parent::__set($strKey, $varValue);
                break;
        }
    }


    /**
     * Validate input and set value
     */
    public function validate()
    {
        $mandatory = $this->mandatory;
        $options = deserialize($this->getPost($this->strName));

        // Check "enabled" only (values can be empty)
        if (is_array($options))
        {
            foreach ($options as $key=>$option)
            {
                $options[$key]['label'] = trim($option['label']);

                if ($options[$key]['enabled'])
                {
                    $this->mandatory = false;
                }
            }
        }

        $varInput = $this->validator($options);

        if (!$this->hasErrors())
        {
            $this->varValue = $varInput;
        }

        // Reset the property
        if ($mandatory)
        {
            $this->mandatory = true;
        }
    }


    /**
     * Generate the widget and return it as string
     * @return string
     */
    public function generate()
    {
        $arrButtons = array('drag', 'up', 'down');
        $strCommand = 'cmd_' . $this->strField;

        // Change the order
        if (\Input::get($strCommand) && is_numeric(\Input::get('cid')) && \Input::get('id') == $this->currentRecord)
        {
            switch (\Input::get($strCommand))
            {
                case 'up':
                    $this->varValue = array_move_up($this->varValue, \Input::get('cid'));
                    break;

                case 'down':
                    $this->varValue = array_move_down($this->varValue, \Input::get('cid'));
                    break;
            }

            \Database::getInstance()->prepare("UPDATE " . $this->strTable . " SET " . $this->strField . "=? WHERE id=?")
                                    ->execute(serialize($this->varValue), $this->currentRecord);

            \Controller::redirect(preg_replace('/&(amp;)?cid=[^&]*/i', '', preg_replace('/&(amp;)?' . preg_quote($strCommand, '/') . '=[^&]*/i', '', \Environment::get('request'))));
        }

        // Sort options
        if ($this->varValue)
        {
            $arrOptions = array();

            // Move selected and sorted options to the top
            foreach ($this->varValue as $i=>$arrOption)
            {
                $arrOptions[$i] = $arrOption['value'];
                unset($this->arrOptions[array_search($arrOption['value'], $this->arrOptions)]);
            }

            ksort($arrOptions);
            $this->arrOptions = array_merge($arrOptions, $this->arrOptions);
        }

        // Begin table
        $return = '<table class="tl_fieldwizard" id="ctrl_'.$this->strId.'">
  <thead>
    <tr>
      <th class="col_0">a)</th>
      <th class="col_1">&nbsp;</th>
      <th class="col_2">b)</th>
      <th class="col_3">c)</th>
      <th class="col_4">&nbsp;</th>
    </tr>
  </thead>
  <tbody class="sortable">';

        $tabindex = 0;

        // Add fields
        foreach ($this->arrOptions as $i=>$option)
        {
            $return .= '
    <tr>
      <td class="col_0"><input type="hidden" name="'.$this->strId.'['.$i.'][enabled]" value=""><input type="checkbox" name="'.$this->strId.'['.$i.'][enabled]" id="'.$this->strId.'_enabled_'.$i.'" class="fw_checkbox" tabindex="'.++$tabindex.'" value="1"'.($this->varValue[$i]['enabled'] ? ' checked="checked"' : '').'></td>
      <td class="col_1"><input type="hidden" name="'.$this->strId.'['.$i.'][value]" value="'.$option.'"><label for="'.$this->strId.'_enabled_'.$i.'">'.($GLOBALS['TL_DCA'][$this->table]['fields'][$option]['label'][0] ? $GLOBALS['TL_DCA'][$this->table]['fields'][$option]['label'][0] : $option).'</label></td>
      <td class="col_2"><input type="text" name="'.$this->strId.'['.$i.'][label]" id="'.$this->strId.'_label_'.$i.'" class="tl_text_4" tabindex="'.++$tabindex.'" value="'.specialchars($this->varValue[$i]['label']).'"></td>
      <td class="col_3"><input type="hidden" name="'.$this->strId.'['.$i.'][mandatory]" value=""><input type="checkbox" name="'.$this->strId.'['.$i.'][mandatory]" id="'.$this->strId.'_mandatory_'.$i.'" class="fw_checkbox" tabindex="'.++$tabindex.'" value="1"'.($this->varValue[$i]['mandatory'] ? ' checked="checked"' : '').'> <label for="'.$this->strId.'_mandatory_'.$i.'"></label></td>';

            // Add row buttons
            $return .= '
      <td class="col_4" style="white-space:nowrap; padding-left:3px;">';

            foreach ($arrButtons as $button)
            {
                $class = ($button == 'up' || $button == 'down') ? ' class="button-move"' : '';

                if ($button == 'drag') {
                    $return .= \Image::getHtml('drag.gif', '', 'class="drag-handle" title="' . sprintf($GLOBALS['TL_LANG']['MSC']['move']) . '"');
                } else {
                    $return .= '<a href="'.$this->addToUrl('&amp;'.$strCommand.'='.$button.'&amp;cid='.$i.'&amp;id='.$this->currentRecord).'"' . $class . ' title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable][$button][0]).'" onclick="Isotope.fieldWizard(this, \''.$button.'\', \'ctrl_'.$this->strId.'\'); return false;">'.\Image::getHtml($button.'.gif', $GLOBALS['TL_LANG'][$this->strTable][$button][0]).'</a> ';
                }
            }

            $return .= '</td>
    </tr>';
        }

        return $return.'
  </tbody>
  </table>';
    }
}

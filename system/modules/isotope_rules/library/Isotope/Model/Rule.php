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

namespace Isotope\Model;

use Isotope\Isotope;
use Isotope\Translation;
use Isotope\Interfaces\IsotopeProduct;

/**
 * Class Payment
 *
 * Implements payment surcharge in product collection
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class Rule extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_rule';

    /**
     * Get label for rule
     * @return  string
     */
    public function getLabel()
    {
        return Translation::get(($this->label ?: $this->name));
    }

    /**
     * Return true if the rule has a percentage (not fixed) amount
     * @return bool
     */
    public function isPercentage()
    {
        return (substr($this->discount, -1) == '%') ? true : false;
    }

    /**
     * Return percentage amount (if applicable)
     * @return float
     * @throws UnexpectedValueException
     */
    public function getPercentage()
    {
        if (!$this->isPercentage())
        {
            throw new \UnexpectedValueException('Rule does not have a percentage amount.');
        }

        return (float) substr($this->discount, 0, -1);
    }

    /**
     * Return percentage label if price is percentage
     * @return  string
     */
    public function getPercentageLabel()
    {
        return $this->isPercentage() ? $this->discount : '';
    }


    public static function findByProduct(IsotopeProduct $objProduct, $strField, $fltPrice)
    {
        return static::findByConditions(array("type='product'"), array(), array($objProduct), ($strField == 'low_price' ? true : false), array($strField => $fltPrice));
    }


    public static function findForCart()
    {
        return static::findByConditions(array("type='cart'", "enableCode=''"));
    }


    public static function findForCartWithCoupons()
    {
        return static::findByConditions(array("type='cart'", "enableCode='1'"));
    }

    public static function findActiveWitoutCoupons()
    {
        return static::findByConditions(array("(type='product' OR (type='cart' AND enableCode=''))"));
    }


    public static function findOneByCouponCode($strCode, $arrCollectionItems)
    {
        $objRules = static::findByConditions(array("type='cart'", "enableCode='1'", 'code=?'), array($strCode), $arrCollectionItems);

        if (null !== $objRules) {
            return $objRules->current();
        }

        return null;
    }


    /**
     * Fetch rules
     */
    protected static function findByConditions($arrProcedures, $arrValues=array(), $arrProducts=null, $blnIncludeVariants=false, $arrAttributeData=array())
    {
        // Only enabled rules
        $arrProcedures[] = "enabled='1'";

        // Date & Time restrictions
        $date = date('Y-m-d');
		$time = date('H:i:s');
		$arrProcedures[] = "(startDate='' OR startDate <= UNIX_TIMESTAMP('$date'))";
		$arrProcedures[] = "(endDate='' OR endDate >= UNIX_TIMESTAMP('$date'))";
		$arrProcedures[] = "(startTime='' OR startTime <= UNIX_TIMESTAMP('1970-01-01 $time'))";
		$arrProcedures[] = "(endTime='' OR endTime >= UNIX_TIMESTAMP('1970-01-01 $time'))";


        // Limits
        $arrProcedures[] = "(limitPerConfig=0 OR limitPerConfig>(SELECT COUNT(*) FROM tl_iso_rule_usage WHERE pid=r.id AND config_id=".(int) Isotope::getConfig()->id." AND order_id NOT IN (SELECT id FROM tl_iso_product_collection WHERE type='order' AND source_collection_id=".(int) Isotope::getCart()->id.")))";

        if (Isotope::getCart()->pid > 0)
        {
            $arrProcedures[] = "(limitPerMember=0 OR limitPerMember>(SELECT COUNT(*) FROM tl_iso_rule_usage WHERE pid=r.id AND member_id=".(int) \FrontendUser::getInstance()->id." AND order_id NOT IN (SELECT id FROM tl_iso_product_collection WHERE type='order' AND source_collection_id=".(int) Isotope::getCart()->id.")))";
        }

        // Store config restrictions
        $arrProcedures[] = "(configRestrictions=''
                            OR (configRestrictions='1' AND configCondition='' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='configs' AND object_id=".(int) Isotope::getConfig()->id.")>0)
                            OR (configRestrictions='1' AND configCondition='1' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='configs' AND object_id=".(int) Isotope::getConfig()->id.")=0))";


        // Member restrictions
        if (Isotope::getCart()->pid > 0)
        {

            $arrGroups = array_map('intval', deserialize(\FrontendUser::getInstance()->groups, true));

            $arrProcedures[] = "(memberRestrictions='none'
                                OR (memberRestrictions='guests' AND memberCondition='1')
                                OR (memberRestrictions='members' AND memberCondition='' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='members' AND object_id=".(int) \FrontendUser::getInstance()->id.")>0)
                                OR (memberRestrictions='members' AND memberCondition='1' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='members' AND object_id=".(int) \FrontendUser::getInstance()->id.")=0)
                                " . (!empty($arrGroups) ? "
                                OR (memberRestrictions='groups' AND memberCondition='' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='groups' AND object_id IN (" . implode(',', $arrGroups) . "))>0)
                                OR (memberRestrictions='groups' AND memberCondition='1' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='groups' AND object_id IN (" . implode(',', $arrGroups) . "))=0)" : '') . ")";
        }
        else
        {
            $arrProcedures[] = "(memberRestrictions='none' OR (memberRestrictions='guests' AND memberCondition=''))";
        }


        // Product restrictions
        if (!is_array($arrProducts))
        {
            $arrProducts = Isotope::getCart()->getItems();
        }

        if (!empty($arrProducts))
        {
            $arrProductIds = array();
            $arrVariantIds = array();
            $arrAttributes = array();
            $arrTypes = array();

            // Prepare product attribute condition
            $objAttributeRules = \Database::getInstance()->execute("SELECT * FROM " . static::$strTable . " WHERE enabled='1' AND productRestrictions='attribute' AND attributeName!='' GROUP BY attributeName, attributeCondition");
            while ($objAttributeRules->next())
            {
                $arrAttributes[] = array
                (
                    'attribute'    => $objAttributeRules->attributeName,
                    'condition'    => $objAttributeRules->attributeCondition,
                    'values'    => array(),
                );
            }

            foreach ($arrProducts as $objProduct)
            {
                if ($objProduct instanceof ProductCollectionItem) {
                    if (!$objProduct->hasProduct()) {
                        continue;
                    }

                    $objProduct = $objProduct->getProduct();
                }

                $arrProductIds[] = $objProduct->getProductId();
                $arrVariantIds[] = $objProduct->{$objProduct->getPk()};
                $arrTypes[] = $objProduct->type;

                if ($objProduct->isVariant()) {
                    $arrVariantIds[] = $objProduct->pid;
                }

                if ($blnIncludeVariants && $objProduct->hasVariants()) {
                    $arrVariantIds = array_merge($arrVariantIds, $objProduct->getVariantIds());
                }

                $arrOptions = $objProduct->getOptions();
                foreach ($arrAttributes as $k => $restriction)
                {
                    $varValue = null;

                    if (isset($arrAttributeData[$restriction['attribute']]))
                    {
                        $varValue = $arrAttributeData[$restriction['attribute']];
                    }
                    elseif (isset($arrOptions[$restriction['attribute']]))
                    {
                        $varValue = $arrOptions[$restriction['attribute']];
                    }
                    else
                    {
                        $varValue = $objProduct->{$restriction['attribute']};
                    }

                    if (!is_null($varValue))
                    {
                        $arrAttributes[$k]['values'][] = is_array($varValue) ? serialize($varValue) : $varValue;
                    }
                }
            }

            $arrProductIds = array_unique($arrProductIds);
            $arrVariantIds = array_unique($arrVariantIds);

            $arrRestrictions = array("productRestrictions='none'");
            $arrRestrictions[] = "(productRestrictions='producttypes' AND productCondition='' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='producttypes' AND object_id IN (" . implode(',', $arrTypes) . "))>0)";
            $arrRestrictions[] = "(productRestrictions='producttypes' AND productCondition='1' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='producttypes' AND object_id IN (" . implode(',', $arrTypes) . "))=0)";
            $arrRestrictions[] = "(productRestrictions='products' AND productCondition='' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='products' AND object_id IN (" . implode(',', $arrProductIds) . "))>0)";
            $arrRestrictions[] = "(productRestrictions='products' AND productCondition='1' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='products' AND object_id IN (" . implode(',', $arrProductIds) . "))=0)";
            $arrRestrictions[] = "(productRestrictions='variants' AND productCondition='' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='variants' AND object_id IN (" . implode(',', $arrVariantIds) . "))>0)";
            $arrRestrictions[] = "(productRestrictions='variants' AND productCondition='1' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='variants' AND object_id IN (" . implode(',', $arrVariantIds) . "))=0)";
            $arrRestrictions[] = "(productRestrictions='pages' AND productCondition='' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='pages' AND object_id IN (SELECT page_id FROM " . \Isotope\Model\ProductCategory::getTable() . " WHERE pid IN (" . implode(',', $arrProductIds) . ")))>0)";
            $arrRestrictions[] = "(productRestrictions='pages' AND productCondition='1' AND (SELECT COUNT(*) FROM tl_iso_rule_restriction WHERE pid=r.id AND type='pages' AND object_id IN (SELECT page_id FROM " . \Isotope\Model\ProductCategory::getTable() . " WHERE pid IN (" . implode(',', $arrProductIds) . ")))=0)";

            foreach ($arrAttributes as $restriction)
            {
                if (empty($restriction['values']))
                    continue;

                $strRestriction = "(productRestrictions='attribute' AND attributeName='" . $restriction['attribute'] . "' AND attributeCondition='" . $restriction['condition'] . "' AND ";

                switch ($restriction['condition'])
                {
                    case 'eq':
                    case 'neq':
                        $strRestriction .= "attributeValue" . ($restriction['condition'] == 'neq' ? " NOT" : '') . " IN ('" . implode("','", array_map('mysql_real_escape_string', $restriction['values'])) . "')";
                        break;

                    case 'lt':
                    case 'gt':
                    case 'elt':
                    case 'egt':
                        $arrOR = array();
                        foreach ($restriction['values'] as $value)
                        {
                            $arrOR[] = "attributeValue" . (($restriction['condition'] == 'lt' || $restriction['condition'] == 'elt') ? '>' : '<') . (($restriction['condition'] == 'elt' || $restriction['condition'] == 'egt') ? '=' : '') . '?';
                            $arrValues[] = $value;
                        }
                        $strRestriction .= '(' . implode(' OR ', $arrOR) . ')';
                        break;

                    case 'starts':
                    case 'ends':
                    case 'contains':
                        $arrOR = array();
                        foreach ($restriction['values'] as $value)
                        {
                            $arrOR[] = "? LIKE CONCAT(" . (($restriction['condition'] == 'ends' || $restriction['condition'] == 'contains') ? "'%', " : '') . "attributeValue" . (($restriction['condition'] == 'starts' || $restriction['condition'] == 'contains') ? ", '%'" : '') . ")";
                            $arrValues[] = $value;
                        }
                        $strRestriction .= '(' . implode(' OR ', $arrOR) . ')';
                        break;

                    default:
                        throw new \InvalidArgumentException('Unknown rule condition "' . $restriction['condition'] . '"');
                }

                $arrRestrictions[] = $strRestriction . ')';
            }

            $arrProcedures[] = '(' . implode(' OR ', $arrRestrictions) . ')';
        }

        $objResult = \Database::getInstance()->prepare("SELECT * FROM " . static::$strTable . " r WHERE " . implode(' AND ', $arrProcedures))->execute($arrValues);

        if ($objResult->numRows) {
            return \Model\Collection::createFromDbResult($objResult, static::$strTable);
        }

        return null;
    }
}

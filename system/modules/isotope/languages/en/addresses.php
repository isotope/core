<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Address formatting for different countries
 *
 * ATTENTION: This file is not supposed to be translated.
 * Address formatting is the same for all languages, but we only want this data to be loaded when needed needed.
 */
$GLOBALS['ISO_ADR']['generic'] =
'<div class="vcard">{if hcard_org!=""}##hcard_org##
{endif}
{if hcard_n=="1"}<div class="n">##hcard_honorific_prefix## ##hcard_given_name## ##hcard_family_name##</div>{endif}

{if hcard_adr=="1"}<div class="adr">##hcard_street_address##

##hcard_postal_code## ##hcard_locality##

##hcard_country_name##</div>{endif}


##hcard_tel##

##hcard_email##</div>';

$GLOBALS['ISO_ADR']['it'] =
'<div class="vcard">{if hcard_org!=""}##hcard_org##
{endif}
{if hcard_n=="1"}<div class="n">##hcard_honorific_prefix## ##hcard_given_name## ##hcard_family_name##</div>{endif}

{if hcard_adr=="1"}<div class="adr">##hcard_street_address##

##hcard_postal_code## ##hcard_locality## {if hcard_region!=""}(##hcard_region##){endif}

##hcard_country_name##</div>{endif}


##hcard_tel##

##hcard_email##</div>';

$GLOBALS['ISO_ADR']['gb'] =
'<div class="vcard">{if hcard_org!=""}##hcard_org##
{endif}
{if hcard_n=="1"}<div class="n">##hcard_honorific_prefix## ##hcard_given_name## ##hcard_family_name##</div>{endif}

{if hcard_adr=="1"}<div class="adr">##hcard_street_address##

##hcard_region## {if outputFormat=="html"} <br> {else} <br /> {endif}

##hcard_locality## {if outputFormat=="html"} <br> {else} <br /> {endif}

##hcard_postal_code##

##hcard_country_name##</div>{endif}


##hcard_tel##

##hcard_email##</div>

<!--
##city##
##state##
##postal##
##country##

##phone##
##email##
-->';

$GLOBALS['ISO_ADR']['us'] =
'<div class="vcard">{if hcard_org!=""}##hcard_org##
{endif}
{if hcard_n=="1"}<div class="n">##hcard_honorific_prefix## ##hcard_given_name## ##hcard_family_name##</div>{endif}

{if hcard_adr=="1"}<div class="adr">##hcard_street_address##

##hcard_locality##, ##hcard_region_abbr## ##hcard_postal_code##

##hcard_country_name##</div>{endif}


##hcard_tel##

##hcard_email##</div>';


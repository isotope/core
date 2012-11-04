<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
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

##hcard_email##</div>';

$GLOBALS['ISO_ADR']['us'] =
'<div class="vcard">{if hcard_org!=""}##hcard_org##
{endif}
{if hcard_n=="1"}<div class="n">##hcard_honorific_prefix## ##hcard_given_name## ##hcard_family_name##</div>{endif}

{if hcard_adr=="1"}<div class="adr">##hcard_street_address##

##hcard_locality##, ##hcard_region_abbr## ##hcard_postal_code##

##hcard_country_name##</div>{endif}


##hcard_tel##

##hcard_email##</div>';

$GLOBALS['ISO_ADR']['eg'] =
'<div class="vcard">{if hcard_org!=""}##hcard_org##
{endif}
{if hcard_n=="1"}<div class="n">##hcard_honorific_prefix## ##hcard_given_name## ##hcard_family_name##</div>{endif}

{if hcard_adr=="1"}<div class="adr">##hcard_street_address##

##hcard_postal_code## ##hcard_locality##{if hcard_region!=""}

<br>##hcard_region##
{endif}

##hcard_country_name##</div>{endif}


##hcard_tel##

##hcard_email##</div>';

$GLOBALS['ISO_ADR']['th'] =
'<div class="vcard">{if hcard_org!=""}##hcard_org##
{endif}
{if hcard_n=="1"}<div class="n">##hcard_honorific_prefix## ##hcard_given_name## ##hcard_family_name##</div>{endif}

{if hcard_adr=="1"}<div class="adr">##hcard_street_address##

##hcard_postal_code## ##hcard_locality##{if hcard_region!=""}

<br>##hcard_region##
{endif}

##hcard_country_name##</div>{endif}


##hcard_tel##

##hcard_email##</div>';


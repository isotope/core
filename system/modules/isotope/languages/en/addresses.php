<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * Address formatting for different countries
 *
 * ATTENTION: This file is not supposed to be translated.
 * Address formatting is the same for all languages, but we only want this data to be loaded when needed.
 */
$GLOBALS['ISO_ADR']['generic'] =
'<div class="vcard">{if hcard_org!=""}##hcard_org##
{endif}{if hcard_n=="1"}<div class="n">{if hcard_honorific_prefix!=""}##hcard_honorific_prefix## {endif}##hcard_given_name## ##hcard_family_name##</div>
{endif}{if hcard_adr=="1"}<div class="adr">##hcard_street_address##
##hcard_postal_code## ##hcard_locality##
##hcard_country_name##</div>{endif}
{if hcard_tel!="" || hcard_email!=""}
<br>
{if hcard_tel!=""}<br>##hcard_tel##{endif}
{if hcard_email!=""}<br>##hcard_email##{endif}
{endif}
</div>';

$GLOBALS['ISO_ADR']['it'] =
'<div class="vcard">{if hcard_org!=""}##hcard_org##
{endif}{if hcard_n=="1"}<div class="n">{if hcard_honorific_prefix!=""}##hcard_honorific_prefix## {endif}##hcard_given_name## ##hcard_family_name##</div>
{endif}{if hcard_adr=="1"}<div class="adr">##hcard_street_address##
##hcard_postal_code## ##hcard_locality## {if hcard_region!=""}(##hcard_region##){endif}

##hcard_country_name##</div>{endif}
{if hcard_tel!="" || hcard_email!=""}
<br>
{if hcard_tel!=""}<br>##hcard_tel##{endif}
{if hcard_email!=""}<br>##hcard_email##{endif}
{endif}
</div>';

$GLOBALS['ISO_ADR']['gb'] =
'<div class="vcard">{if hcard_org!=""}##hcard_org##
{endif}{if hcard_n=="1"}<div class="n">{if hcard_honorific_prefix!=""}##hcard_honorific_prefix## {endif}##hcard_given_name## ##hcard_family_name##</div>
{endif}{if hcard_adr=="1"}<div class="adr">##hcard_street_address##
##hcard_region## {if outputFormat=="html"} <br> {else} <br /> {endif}

##hcard_locality## {if outputFormat=="html"} <br> {else} <br /> {endif}

##hcard_postal_code##
##hcard_country_name##</div>{endif}
{if hcard_tel!="" || hcard_email!=""}
<br>
{if hcard_tel!=""}<br>##hcard_tel##{endif}
{if hcard_email!=""}<br>##hcard_email##{endif}
{endif}
</div>';

$GLOBALS['ISO_ADR']['us'] =
'<div class="vcard">{if hcard_org!=""}##hcard_org##
{endif}{if hcard_n=="1"}<div class="n">{if hcard_honorific_prefix!=""}##hcard_honorific_prefix## {endif}##hcard_given_name## ##hcard_family_name##</div>
{endif}{if hcard_adr=="1"}<div class="adr">##hcard_street_address##
##hcard_locality##, ##hcard_region_abbr## ##hcard_postal_code##
##hcard_country_name##</div>{endif}
{if hcard_tel!="" || hcard_email!=""}
<br>
{if hcard_tel!=""}<br>##hcard_tel##{endif}
{if hcard_email!=""}<br>##hcard_email##{endif}
{endif}
</div>';

$GLOBALS['ISO_ADR']['eg'] =
'<div class="vcard">{if hcard_org!=""}##hcard_org##
{endif}{if hcard_n=="1"}<div class="n">{if hcard_honorific_prefix!=""}##hcard_honorific_prefix## {endif}##hcard_given_name## ##hcard_family_name##</div>
{endif}{if hcard_adr=="1"}<div class="adr">##hcard_street_address##
##hcard_postal_code## ##hcard_locality##{if hcard_region!=""}

<br>##hcard_region##
{endif}

##hcard_country_name##</div>{endif}
{if hcard_tel!="" || hcard_email!=""}
<br>
{if hcard_tel!=""}<br>##hcard_tel##{endif}
{if hcard_email!=""}<br>##hcard_email##{endif}
{endif}
</div>';

$GLOBALS['ISO_ADR']['th'] =
'<div class="vcard">{if hcard_org!=""}##hcard_org##
{endif}{if hcard_n=="1"}<div class="n">{if hcard_honorific_prefix!=""}##hcard_honorific_prefix## {endif}##hcard_given_name## ##hcard_family_name##</div>
{endif}{if hcard_adr=="1"}<div class="adr">##hcard_street_address##
##hcard_postal_code## ##hcard_locality##{if hcard_region!=""}

<br>##hcard_region##
{endif}

##hcard_country_name##</div>{endif}
{if hcard_tel!="" || hcard_email!=""}
<br>
{if hcard_tel!=""}<br>##hcard_tel##{endif}
{if hcard_email!=""}<br>##hcard_email##{endif}
{endif}
</div>';

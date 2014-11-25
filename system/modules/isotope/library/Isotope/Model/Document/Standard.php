<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Document;

use Haste\Haste;
use Isotope\Interfaces\IsotopeDocument;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Document;

/**
 * Class Standard
 *
 * Provide methods to handle Isotope galleries.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Yanick Witschi <yanick.witschi@terminal42.ch>
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class Standard extends Document implements IsotopeDocument
{

    /**
     * {@inheritdoc}
     */
    public function outputToBrowser(IsotopeProductCollection $objCollection)
    {
        $arrTokens  = $this->prepareCollectionTokens($objCollection);
        $pdf        = $this->generatePDF($objCollection, $arrTokens);

        $pdf->Output(
            $this->prepareFileName($this->fileTitle, $arrTokens) . '.pdf',
            'D'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function outputToFile(IsotopeProductCollection $objCollection, $strDirectoryPath)
    {
        $arrTokens  = $this->prepareCollectionTokens($objCollection);
        $pdf        = $this->generatePDF($objCollection, $arrTokens);
        $strFile    = $this->prepareFileName($this->fileTitle, $arrTokens, $strDirectoryPath) . '.pdf';

        $pdf->Output(
            $strFile,
            'F'
        );

        return $strFile;
    }

    /**
     * Generate the pdf document
     * @param   IsotopeProductCollection
     * @param   array
     * @return  \TCPDF
     */
    protected function generatePDF(IsotopeProductCollection $objCollection, array $arrTokens)
    {
        // TCPDF configuration
        $l                    = array();
        $l['a_meta_dir']      = 'ltr';
        $l['a_meta_charset']  = $GLOBALS['TL_CONFIG']['characterSet'];
        $l['a_meta_language'] = substr($GLOBALS['TL_LANGUAGE'], 0, 2);
        $l['w_page']          = 'page';

        // Include TCPDF config
        require_once TL_ROOT . '/system/config/tcpdf.php';

        // Create new PDF document
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true);

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor(PDF_AUTHOR);
        $pdf->SetTitle(\String::parseSimpleTokens($this->documentTitle, $arrTokens));

        // Prevent font subsetting (huge speed improvement)
        $pdf->setFontSubsetting(false);

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

        // Set auto page breaks
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

        // Set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // Set some language-dependent strings
        $pdf->setLanguageArray($l);

        // Initialize document and add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN);

        // Write the HTML content
        $pdf->writeHTML($this->generateTemplate($objCollection, $arrTokens), true, 0, true, 0);

        $pdf->lastPage();

        return $pdf;
    }

    /**
     * Generate and return document template
     * @return  string
     */
    protected function generateTemplate(IsotopeProductCollection $objCollection, array $arrTokens)
    {
        $objTemplate = new \Isotope\Template($this->documentTpl);
        $objTemplate->setData($this->arrData);

        $objTemplate->title      = \String::parseSimpleTokens($this->documentTitle, $arrTokens);
        $objTemplate->collection = $objCollection;

        // Render the collection
        $objCollectionTemplate = new \Isotope\Template($this->collectionTpl);

        $objCollection->addToTemplate(
            $objCollectionTemplate,
            array(
                'gallery' => $this->gallery,
                'sorting' => $objCollection->getItemsSortingCallable($this->orderCollectionBy),
            )
        );

        $objTemplate->products = $objCollectionTemplate->parse();

        // !HOOK: customize the document template
        if (isset($GLOBALS['ISO_HOOKS']['generateDocumentTemplate']) && is_array($GLOBALS['ISO_HOOKS']['generateDocumentTemplate'])) {
            foreach ($GLOBALS['ISO_HOOKS']['generateDocumentTemplate'] as $callback) {
                \System::importStatic($callback[0])->$callback[1]($objTemplate, $objCollection, $this);
            }
        }

        // Generate template and fix PDF issues, see Contao's ModuleArticle
        $strBuffer = Haste::getInstance()->call('replaceInsertTags', array($objTemplate->parse(), false));
        $strBuffer = html_entity_decode($strBuffer, ENT_QUOTES, $GLOBALS['TL_CONFIG']['characterSet']);
        $strBuffer = \Controller::convertRelativeUrls($strBuffer, '', true);

        // Remove form elements and JavaScript links
        $arrSearch = array
        (
            '@<form.*</form>@Us',
            '@<a [^>]*href="[^"]*javascript:[^>]+>.*</a>@Us'
        );

        $strBuffer = preg_replace($arrSearch, '', $strBuffer);

        // URL decode image paths (see contao/core#6411)
        // Make image paths absolute
        $strBuffer = preg_replace_callback('@(src=")([^"]+)(")@', function ($args) {
            if (preg_match('@^(http://|https://)@', $args[2])) {
                return $args[2];
            }
            return $args[1] . TL_ROOT . '/' . rawurldecode($args[2]) . $args[3];
        }, $strBuffer);

        // Handle line breaks in preformatted text
        $strBuffer = preg_replace_callback('@(<pre.*</pre>)@Us', 'nl2br_callback', $strBuffer);

        // Default PDF export using TCPDF
        $arrSearch = array
        (
            '@<span style="text-decoration: ?underline;?">(.*)</span>@Us',
            '@(<img[^>]+>)@',
            '@(<div[^>]+block[^>]+>)@',
            '@[\n\r\t]+@',
            '@<br( /)?><div class="mod_article@',
            '@href="([^"]+)(pdf=[0-9]*(&|&amp;)?)([^"]*)"@'
        );

        $arrReplace = array
        (
            '<u>$1</u>',
            '<br>$1',
            '<br>$1',
            ' ',
            '<div class="mod_article',
            'href="$1$4"'
        );

        $strBuffer = preg_replace($arrSearch, $arrReplace, $strBuffer);

        return $strBuffer;
    }
}

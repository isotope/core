<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Document;

use Isotope\Interfaces\IsotopeDocument;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Document;
use Isotope\Model\ProductCollection;
use Isotope\Template;

class Standard extends Document implements IsotopeDocument
{

    /**
     * {@inheritdoc}
     */
    public function outputToBrowser(IsotopeProductCollection $objCollection)
    {
        $this->prepareEnvironment($objCollection);

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
        $this->prepareEnvironment($objCollection);

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
     *
     * @param IsotopeProductCollection $objCollection
     * @param array                    $arrTokens
     *
     * @return \TCPDF
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
        if (file_exists(TL_ROOT . '/system/config/tcpdf.php')) {
            require_once TL_ROOT . '/system/config/tcpdf.php';
        } elseif (file_exists(TL_ROOT . '/vendor/contao/core-bundle/Resources/contao/config/tcpdf.php')) {
            require_once TL_ROOT . '/vendor/contao/core-bundle/Resources/contao/config/tcpdf.php';
        }

        // Create new PDF document
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true);

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor(PDF_AUTHOR);
        $pdf->SetTitle(\StringUtil::parseSimpleTokens($this->documentTitle, $arrTokens));

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
     *
     * @param IsotopeProductCollection $objCollection
     * @param array                    $arrTokens
     *
     * @return string
     */
    protected function generateTemplate(IsotopeProductCollection $objCollection, array $arrTokens)
    {
        $objPage = \PageModel::findWithDetails($objCollection->page_id);

        /** @var Template|\stdClass $objTemplate */
        $objTemplate = new Template($this->documentTpl);
        $objTemplate->setData($this->arrData);

        $objTemplate->title         = \StringUtil::parseSimpleTokens($this->documentTitle, $arrTokens);
        $objTemplate->collection    = $objCollection;
        $objTemplate->config        = $objCollection->getConfig();
        $objTemplate->page          = $objPage;
        $objTemplate->dateFormat    = $objPage->dateFormat ?: $GLOBALS['TL_CONFIG']['dateFormat'];
        $objTemplate->timeFormat    = $objPage->timeFormat ?: $GLOBALS['TL_CONFIG']['timeFormat'];
        $objTemplate->datimFormat   = $objPage->datimFormat ?: $GLOBALS['TL_CONFIG']['datimFormat'];

        // Render the collection
        $objCollectionTemplate = new Template($this->collectionTpl);

        $objCollection->addToTemplate(
            $objCollectionTemplate,
            array(
                'gallery' => $this->gallery,
                'sorting' => ProductCollection::getItemsSortingCallable($this->orderCollectionBy),
            )
        );

        $objTemplate->products = $objCollectionTemplate->parse();

        // !HOOK: customize the document template
        if (isset($GLOBALS['ISO_HOOKS']['generateDocumentTemplate']) && is_array($GLOBALS['ISO_HOOKS']['generateDocumentTemplate'])) {
            foreach ($GLOBALS['ISO_HOOKS']['generateDocumentTemplate'] as $callback) {
                \System::importStatic($callback[0])->{$callback[1]}($objTemplate, $objCollection, $this);
            }
        }

        // Generate template and fix PDF issues, see Contao's ModuleArticle
        $strBuffer = \Controller::replaceInsertTags($objTemplate->parse(), false);
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
                return $args[1] . $args[2] . $args[3];
            }
            if(version_compare(VERSION.'.'.BUILD, '4.0.0', '<=')){ (( see issue #1980
                return $args[1] . TL_ROOT . '/' . rawurldecode($args[2]) . $args[3];
            } else {
                return $args[0];
            }
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

    /**
     * Loads the page configuration and language before generating a PDF.
     *
     * @param IsotopeProductCollection $objCollection
     */
    protected function prepareEnvironment(IsotopeProductCollection $objCollection)
    {
        global $objPage;

        if (!is_object($objPage) && $objCollection->pageId > 0) {
            $objPage = \PageModel::findWithDetails($objCollection->pageId);
            $objPage = \Isotope\Frontend::loadPageConfig($objPage);

            \System::loadLanguageFile('default', $GLOBALS['TL_LANGUAGE'], true);
        }
    }
}

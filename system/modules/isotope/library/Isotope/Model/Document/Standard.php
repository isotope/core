<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Document;

use Contao\Controller;
use Contao\Environment;
use Contao\File;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
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
        define('K_TCPDF_EXTERNAL_CONFIG', true);
        define('K_PATH_MAIN', TL_ROOT . '/vendor/tecnickcom/tcpdf/');
        define('K_PATH_URL', Environment::get('base') . 'vendor/tecnickcom/tcpdf/');
        define('K_PATH_FONTS', K_PATH_MAIN . 'fonts/');
        define('K_PATH_CACHE', TL_ROOT . '/system/tmp/');
        define('K_PATH_URL_CACHE', TL_ROOT . '/system/tmp/');
        define('K_PATH_IMAGES', K_PATH_MAIN . 'images/');
        define('K_BLANK_IMAGE', K_PATH_IMAGES . '_blank.png');
        define('PDF_PAGE_FORMAT', 'A4');
        define('PDF_PAGE_ORIENTATION', 'P');
        define('PDF_CREATOR', 'Contao Open Source CMS');
        define('PDF_AUTHOR', Environment::get('url'));
        define('PDF_HEADER_TITLE', $GLOBALS['TL_CONFIG']['websiteTitle'] ?? '');
        define('PDF_HEADER_STRING', '');
        define('PDF_HEADER_LOGO', '');
        define('PDF_HEADER_LOGO_WIDTH', 30);
        define('PDF_UNIT', 'mm');
        define('PDF_MARGIN_HEADER', 0);
        define('PDF_MARGIN_FOOTER', 0);
        define('PDF_MARGIN_TOP', 10);
        define('PDF_MARGIN_BOTTOM', 10);
        define('PDF_MARGIN_LEFT', 15);
        define('PDF_MARGIN_RIGHT', 15);
        define('PDF_FONT_NAME_MAIN', 'freeserif');
        define('PDF_FONT_SIZE_MAIN', 12);
        define('PDF_FONT_NAME_DATA', 'freeserif');
        define('PDF_FONT_SIZE_DATA', 12);
        define('PDF_FONT_MONOSPACED', 'freemono');
        define('PDF_FONT_SIZE_MONOSPACED', 10); // PATCH
        define('PDF_IMAGE_SCALE_RATIO', 1.25);
        define('HEAD_MAGNIFICATION', 1.1);
        define('K_CELL_HEIGHT_RATIO', 1.25);
        define('K_TITLE_MAGNIFICATION', 1.3);
        define('K_SMALL_RATIO', 2/3);
        define('K_THAI_TOPCHARS', false);
        define('K_TCPDF_CALLS_IN_HTML', false);

        // Create new PDF document
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true);

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor(PDF_AUTHOR);
        $pdf->SetTitle(StringUtil::parseSimpleTokens($this->documentTitle, $arrTokens));

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
        /** @var Template|\stdClass $objTemplate */
        $objTemplate = new Template($this->documentTpl);
        $objTemplate->setData($this->arrData);

        $objTemplate->title         = StringUtil::parseSimpleTokens($this->documentTitle, $arrTokens);
        $objTemplate->collection    = $objCollection;
        $objTemplate->config        = $objCollection->getConfig();
        $objTemplate->dateFormat    = $GLOBALS['TL_CONFIG']['dateFormat'];
        $objTemplate->timeFormat    = $GLOBALS['TL_CONFIG']['timeFormat'];
        $objTemplate->datimFormat   = $GLOBALS['TL_CONFIG']['datimFormat'];

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
        if (isset($GLOBALS['ISO_HOOKS']['generateDocumentTemplate']) && \is_array($GLOBALS['ISO_HOOKS']['generateDocumentTemplate'])) {
            foreach ($GLOBALS['ISO_HOOKS']['generateDocumentTemplate'] as $callback) {
                System::importStatic($callback[0])->{$callback[1]}($objTemplate, $objCollection, $this);
            }
        }

        // Generate template and fix PDF issues, see Contao's ModuleArticle
        $strBuffer = Controller::replaceInsertTags($objTemplate->parse(), false);
        $strBuffer = html_entity_decode($strBuffer, ENT_QUOTES, $GLOBALS['TL_CONFIG']['characterSet']);
        $strBuffer = Controller::convertRelativeUrls($strBuffer, '', true);

        // Remove form elements and JavaScript links
        $arrSearch = array
        (
            '@<form.*</form>@Us',
            '@<a [^>]*href="[^"]*javascript:[^>]+>.*</a>@Us'
        );

        $strBuffer = preg_replace($arrSearch, '', $strBuffer);

        // URL decode image paths (see contao/core#6411)
        // Make image paths absolute
        $blnOverrideRoot = false;
        $strBuffer = preg_replace_callback('@(src=")([^"]+)(")@', function ($args) use (&$blnOverrideRoot) {
            if (preg_match('@^(http://|https://)@', $args[2])) {
                return $args[1] . $args[2] . $args[3];
            }

            $path = rawurldecode($args[2]);

            if (method_exists(File::class, 'createIfDeferred')) {
                (new File($path))->createIfDeferred();
            }

            $blnOverrideRoot = true;
            return $args[1] . TL_ROOT . '/' . $path . $args[3];
        }, $strBuffer);

        if ($blnOverrideRoot) {
            $_SERVER['DOCUMENT_ROOT'] = TL_ROOT;
        }

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

        if (
            !\is_object($objPage)
            && $objCollection->pageId > 0
            && ($objPage = PageModel::findWithDetails($objCollection->pageId))
        ) {
            $objPage = \Isotope\Frontend::loadPageConfig($objPage);

            System::loadLanguageFile('default', $GLOBALS['TL_LANGUAGE'], true);
        }
    }
}

<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2008-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Model\Document;

use Isotope\Interfaces\IsotopeDocument;
use Isotope\Model\Document;
use Isotope\Model\Gallery;

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
    public function printToBrowser()
    {
        $pdf = $this->generatePDF();
        $pdf->Output(sprintf('%s.pdf', \String::parseSimpleTokens($this->fileTitle, $this->arrCollectionTokens)), 'D');
    }

    /**
     * {@inheritdoc}
     */
    public function store($path)
    {
        $pdf = $this->generatePDF();
        $pdf->Output(sprintf(TL_ROOT . '/%s/%s.pdf', $path, \String::parseSimpleTokens($this->fileTitle, $this->arrCollectionTokens)), 'F');
    }

    /**
     * Generate the pdf document
     * @return \TCPDF
     */
    protected function generatePDF()
    {
        // TCPDF configuration
        $l = array();
        $l['a_meta_dir']        = 'ltr';
        $l['a_meta_charset']    = $GLOBALS['TL_CONFIG']['characterSet'];
        $l['a_meta_language']   = $GLOBALS['TL_LANGUAGE'];
        $l['w_page']            = 'page';

        // Include library
        require_once TL_ROOT . '/system/config/tcpdf.php';
        require_once TL_ROOT . '/system/modules/core/vendor/tcpdf/tcpdf.php';

        // Create new PDF document
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true);

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor(PDF_AUTHOR);
        $pdf->SetTitle(\String::parseSimpleTokens($this->documentTitle, $this->arrCollectionTokens));

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

        // Prepare the document
        $objTemplate = new \Isotope\Template($this->documentTpl);

        // Add title
        $objTemplate->title = \String::parseSimpleTokens($this->documentTitle, $this->arrCollectionTokens);

        // Add billing address
        if (($objAddress = $this->collection->getBillingAddress()) !== null) {
            $objTemplate->billingAddress = $objAddress;
        }

        // Render the collection
        $objCollectionTemplate = new \Isotope\Template($this->collectionTpl);
        $this->collection->addToTemplate($objCollectionTemplate);
        $objTemplate->collection = $objCollectionTemplate->parse();

        // Write the HTML content
        $pdf->writeHTML($objTemplate->parse(), true, 0, true, 0);

        $pdf->lastPage();

        return $pdf;
    }
}

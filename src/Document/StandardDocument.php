<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Document;

use Codefog\HasteBundle\StringParser;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\Environment;
use Contao\File;
use Contao\PageModel;
use Contao\System;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsDocument;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\ProductCollection;
use Isotope\Template;

#[AsDocument]
class StandardDocument extends AbstractDocument
{
    public function __construct(
        ContaoFramework $framework,
        private readonly StringParser $stringParser,
        private readonly InsertTagParser $insertTagParser,
        int $id,
        string $fileTitle,
        private readonly string $projectDir,
    ) {
        parent::__construct($framework, $this->stringParser, $id, $fileTitle);
    }

    public function outputToBrowser(IsotopeProductCollection $objCollection): void
    {
        $this->prepareEnvironment($objCollection);

        $arrTokens = $this->prepareCollectionTokens($objCollection);
        $pdf = $this->generatePDF($objCollection, $arrTokens);

        $pdf->Output(
            $this->prepareFileName($this->getFileTitle(), $arrTokens).'.pdf',
            'D',
        );
    }

    public function outputToFile(IsotopeProductCollection $objCollection, string $path): string
    {
        $this->prepareEnvironment($objCollection);

        $arrTokens = $this->prepareCollectionTokens($objCollection);
        $pdf = $this->generatePDF($objCollection, $arrTokens);
        $strFile = $this->prepareFileName($this->getFileTitle(), $arrTokens, $path).'.pdf';

        $pdf->Output(
            $strFile,
            'F',
        );

        return $strFile;
    }

    private function generatePDF(IsotopeProductCollection $objCollection, array $arrTokens): \TCPDF
    {
        // TCPDF configuration
        $l = [];
        $l['a_meta_dir'] = 'ltr';
        $l['a_meta_charset'] = 'UTF-8';
        $l['a_meta_language'] = substr((string) $GLOBALS['TL_LANGUAGE'], 0, 2);
        $l['w_page'] = 'page';

        // Include TCPDF config
        \define('K_TCPDF_EXTERNAL_CONFIG', true);
        \define('K_PATH_MAIN', $this->projectDir.'/vendor/tecnickcom/tcpdf/');
        \define('K_PATH_URL', Environment::get('base').'vendor/tecnickcom/tcpdf/');
        \define('K_PATH_FONTS', K_PATH_MAIN.'fonts/');
        \define('K_PATH_CACHE', $this->projectDir.'/system/tmp/');
        \define('K_PATH_URL_CACHE', $this->projectDir.'/system/tmp/');
        \define('K_PATH_IMAGES', K_PATH_MAIN.'images/');
        \define('K_BLANK_IMAGE', K_PATH_IMAGES.'_blank.png');
        \define('PDF_PAGE_FORMAT', 'A4');
        \define('PDF_PAGE_ORIENTATION', 'P');
        \define('PDF_CREATOR', 'Contao Open Source CMS');
        \define('PDF_AUTHOR', Environment::get('url'));
        \define('PDF_HEADER_TITLE', $GLOBALS['TL_CONFIG']['websiteTitle'] ?? '');
        \define('PDF_HEADER_STRING', '');
        \define('PDF_HEADER_LOGO', '');
        \define('PDF_HEADER_LOGO_WIDTH', 30);
        \define('PDF_UNIT', 'mm');
        \define('PDF_MARGIN_HEADER', 0);
        \define('PDF_MARGIN_FOOTER', 0);
        \define('PDF_MARGIN_TOP', 10);
        \define('PDF_MARGIN_BOTTOM', 10);
        \define('PDF_MARGIN_LEFT', 15);
        \define('PDF_MARGIN_RIGHT', 15);
        \define('PDF_FONT_NAME_MAIN', 'freeserif');
        \define('PDF_FONT_SIZE_MAIN', 12);
        \define('PDF_FONT_NAME_DATA', 'freeserif');
        \define('PDF_FONT_SIZE_DATA', 12);
        \define('PDF_FONT_MONOSPACED', 'freemono');
        \define('PDF_FONT_SIZE_MONOSPACED', 10); // PATCH
        \define('PDF_IMAGE_SCALE_RATIO', 1.25);
        \define('HEAD_MAGNIFICATION', 1.1);
        \define('K_CELL_HEIGHT_RATIO', 1.25);
        \define('K_TITLE_MAGNIFICATION', 1.3);
        \define('K_SMALL_RATIO', 2 / 3);
        \define('K_THAI_TOPCHARS', false);
        \define('K_TCPDF_CALLS_IN_HTML', false);

        // Create new PDF document
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true);

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor(PDF_AUTHOR);
        $pdf->SetTitle($this->stringParser->recursiveReplaceTokensAndTags($this->getDocumentTitle(), $arrTokens));

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

    private function generateTemplate(IsotopeProductCollection $objCollection, array $arrTokens): string
    {
        $objPage = PageModel::findWithDetails($objCollection->pageId);

        /** @var Template|\stdClass $objTemplate */
        $objTemplate = new Template($this->getDocumentTemplate());

        $objTemplate->title = $this->stringParser->recursiveReplaceTokensAndTags($this->getDocumentTitle(), $arrTokens);
        $objTemplate->document = $this;
        $objTemplate->collection = $objCollection;
        $objTemplate->config = $objCollection->getConfig();
        $objTemplate->page = $objPage;
        $objTemplate->dateFormat = $objPage->dateFormat ?? $GLOBALS['TL_CONFIG']['dateFormat'];
        $objTemplate->timeFormat = $objPage->timeFormat ?? $GLOBALS['TL_CONFIG']['timeFormat'];
        $objTemplate->datimFormat = $objPage->datimFormat ?? $GLOBALS['TL_CONFIG']['datimFormat'];

        // Render the collection
        $objCollectionTemplate = new Template($this->getCollectionTemplate());

        $objCollection->addToTemplate($objCollectionTemplate, [
            'gallery' => $this->getGalleryId(),
            'sorting' => ProductCollection::getItemsSortingCallable($this->getOrderCollectionBy()),
        ]);

        $objTemplate->products = $objCollectionTemplate->parse();

        // !HOOK: customize the document template
        if (isset($GLOBALS['ISO_HOOKS']['generateDocumentTemplate']) && \is_array($GLOBALS['ISO_HOOKS']['generateDocumentTemplate'])) {
            foreach ($GLOBALS['ISO_HOOKS']['generateDocumentTemplate'] as $callback) {
                System::importStatic($callback[0])->{$callback[1]}($objTemplate, $objCollection, $this);
            }
        }

        // Generate template and fix PDF issues, see Contao's ModuleArticle
        $strBuffer = $this->insertTagParser->replaceInline($objTemplate->parse());
        $strBuffer = html_entity_decode($strBuffer, ENT_QUOTES, 'UTF-8');
        $strBuffer = Controller::convertRelativeUrls($strBuffer, '', true);

        // Remove form elements and JavaScript links
        $arrSearch =
        [
            '@<form.*</form>@Us',
            '@<a [^>]*href="[^"]*javascript:[^>]+>.*</a>@Us',
        ];

        $strBuffer = preg_replace($arrSearch, '', $strBuffer);

        // URL decode image paths (see contao/core#6411)
        // Make image paths absolute
        $blnOverrideRoot = false;
        $strBuffer = preg_replace_callback(
            '@(src=")([^"]+)(")@',
            function ($args) use (&$blnOverrideRoot) {
                if (preg_match('@^(http://|https://)@', $args[2])) {
                    return $args[1].$args[2].$args[3];
                }

                $path = rawurldecode($args[2]);

                if (method_exists(File::class, 'createIfDeferred')) {
                    (new File($path))->createIfDeferred();
                }

                $blnOverrideRoot = true;

                return $args[1].$this->projectDir.'/'.$path.$args[3];
            },
            (string) $strBuffer,
        );

        if ($blnOverrideRoot) {
            $_SERVER['DOCUMENT_ROOT'] = $this->projectDir;
        }

        // Handle line breaks in preformatted text
        $strBuffer = preg_replace_callback('@(<pre.*</pre>)@Us', static fn ($matches) => str_replace("\n", '<br>', $matches[0]), (string) $strBuffer);

        // Default PDF export using TCPDF
        $arrSearch =
        [
            '@<span style="text-decoration: ?underline;?">(.*)</span>@Us',
            '@(<img[^>]+>)@',
            '@(<div[^>]+block[^>]+>)@',
            '@[\n\r\t]+@',
            '@<br( /)?><div class="mod_article@',
            '@href="([^"]+)(pdf=[0-9]*(&|&amp;)?)([^"]*)"@',
        ];

        $arrReplace =
        [
            '<u>$1</u>',
            '<br>$1',
            '<br>$1',
            ' ',
            '<div class="mod_article',
            'href="$1$4"',
        ];

        return preg_replace($arrSearch, $arrReplace, (string) $strBuffer);
    }
}

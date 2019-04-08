<?php

namespace Isotope\Backend\Document;

use Contao\BackendTemplate;
use Contao\Controller;
use Haste\Util\Url;
use Isotope\Model\Document;

class TcpdfCheck
{

    public function redirectIfEmpty()
    {
        $types = Document::getModelTypes();

        if (empty($types)) {
            Controller::redirect(Url::addQueryString('key=empty'));
        }
    }

    public function showEmptyWarning()
    {
        $template = new BackendTemplate('be_iso_tcpdf');

        return $template->parse();
    }
}

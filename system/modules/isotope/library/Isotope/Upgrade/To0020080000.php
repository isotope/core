<?php

namespace Isotope\Upgrade;

use Contao\Database;

class To0020080000 extends Base
{
    public function run($blnInstalled)
    {
        if (!$blnInstalled) {
            return;
        }

        if (Database::getInstance()->fieldExists('iso_setReaderJumpTo', 'tl_page', true) && $this->createDatabaseField('iso_readerMode', 'tl_page')) {
            Database::getInstance()->execute("UPDATE tl_page SET iso_readerMode='page' WHERE iso_setReaderJumpTo='1'");
        }
    }
}

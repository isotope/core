<?php

namespace Isotope\Upgrade;

use Contao\Database;

class To0020090000 extends Base
{
    public function run($blnInstalled)
    {
        if (!$blnInstalled) {
            return;
        }

        if (
            !Database::getInstance()->tableExists('tl_iso_requestcache')
            || !Database::getInstance()->fieldExists('config', 'tl_iso_requestcache')
            || Database::getInstance()->fieldExists('config_hash', 'tl_iso_requestcache')
        ) {
            return;
        }

        Database::getInstance()->execute("ALTER TABLE tl_iso_requestcache ADD COLUMN config_hash varchar(32) NOT NULL default ''");
        Database::getInstance()->execute('UPDATE tl_iso_requestcache SET config_hash=MD5(config)');
    }
}

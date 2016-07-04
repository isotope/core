<?php

foreach ([
    '../../initialize.php',
    '../../../../../../../system/initialize.php',
    '../../../../../../system/initialize.php',
         ] as $script
) {
    if (file_exists($script)) {
        require_once($script);
        return;
    }
}

die('Contao initialize.php was not found');

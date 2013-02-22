<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope;


/**
 * Class ModuleIsotopeTranslation
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Philipp Kaiblinger <philipp.kaiblinger@kaipo.at>
 */
class ModuleIsotopeTranslation extends \BackendModule
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'be_isotope_translation';


    public function generate()
    {
        $this->import('BackendUser', 'User');
        $this->import('Files');

        if (!strlen($this->User->translation))
        {
            return '<p class="tl_gerror">' . $GLOBALS['TL_LANG']['ERR']['noLanguageForTranslation'] . '</p>';
        }

        if (\Input::get('act') == 'download')
        {
            return $this->export();
        }

        return parent::generate();
    }


    /**
     * Generate module
     */
    protected function compile()
    {
        $this->import('Session');

        if (\Input::post('FORM_SUBMIT') == 'tl_translation_filters')
        {
            $arrFilter['filter_translation']['isotope_translation'] = array
            (
                'module'    => \Input::post('module'),
                'file'        => \Input::post('file'),
            );

            $this->Session->appendData($arrFilter);

            $this->reload();
        }

        $arrSession = $this->Session->get('filter_translation');
        $arrSession = $arrSession['isotope_translation'];


        $this->Template->headline = $GLOBALS['TL_LANG']['MSC']['translationSelect'];
        $this->Template->action = ampersand(\Environment::get('request'));
        $this->Template->slabel = $GLOBALS['TL_LANG']['MSC']['save'];
        $this->Template->theme = $this->getTheme();


        // get modules
        $arrModules = array();
        foreach( \Config::getInstance()->getActiveModules() as $module )
        {
            if (strpos($module, 'isotope') === false)
                continue;

            $arrModules[] = array('value'=>$module, 'label'=>$module,'default'=>($arrSession['module'] == $module ? true : false));
        }

        // get files
        $arrFiles = array();
        if(strlen($arrSession['module']))
        {
            if (!is_dir(TL_ROOT . '/system/modules/' . $arrSession['module']. '/languages/' . $this->User->translation))
            {
                $this->Files->mkdir('system/modules/' . $arrSession['module']. '/languages/' . $this->User->translation);
            }

            $arrFileSearch = scan(TL_ROOT . '/system/modules/' . $arrSession['module']. '/languages/en/');

            foreach ($arrFileSearch as $file)
            {
                if (in_array($file, array('countries.php')))
                    continue;

                $arrFiles[] = array('value'=>$file, 'label'=>$file, 'default'=>($arrSession['file'] == $file ? true : false));
            }
        }


        if (is_file(TL_ROOT . '/system/modules/' . $arrSession['module']. '/languages/en/' . $arrSession['file']))
        {
            $arrSource = $this->parseFile(TL_ROOT . '/system/modules/' . $arrSession['module']. '/languages/en/' . $arrSession['file']);
            $arrTranslation = $this->parseFile(TL_ROOT . '/system/modules/' . $arrSession['module']. '/languages/' . $this->User->translation . '/' . $arrSession['file']);

            if (\Input::post('FORM_SUBMIT') == 'isotope_translation')
            {
                $strData = '';

                foreach( array_keys($arrSource) as $key )
                {
                    $value = trim(\Input::postRaw(standardize($key)));

                    if (!strlen($value))
                        continue;

                    $value = str_replace(array("\r\n", "\n", "\r", '\n'), '\n', $value, $count);

                    if ($value == $arrTranslation[$key])
                        continue;

                    $strData .= $key . ' = ' . ($count > 0 ? ('"' . str_replace('"', '\"', $value) . '";'."\n") : ("'" . str_replace("'", "\'", $value) . "';\n"));
                }

                if ($strData == '')
                {
                    $this->Files->delete('system/modules/' . $arrSession['module']. '/languages/' . $this->User->translation . '/local/' . $arrSession['file']);
                    $this->reload();
                }

                $objFile = new File('system/modules/' . $arrSession['module']. '/languages/' . $this->User->translation . '/local/' . $arrSession['file']);
                $objFile->write($this->getHeader() . $strData . "\n");
                $objFile->close();

                $_SESSION['TL_CONFIRM'][] = $GLOBALS['TL_LANG']['MSC']['translationSaved'];
                $this->reload();
            }

            $this->Template->edit = true;
            $this->Template->source = $arrSource;
            $this->Template->translation = array_merge($arrTranslation, $this->parseFile(TL_ROOT . '/system/modules/' . $arrSession['module']. '/languages/' . $this->User->translation . '/local/' . $arrSession['file']));
            $this->Template->headline = sprintf($GLOBALS['TL_LANG']['MSC']['translationEdit'], $arrSession['file'], $arrSession['module']);

            if (!is_array($this->Template->source))
            {
                $this->Template->edit = false;
                $this->Template->error = $GLOBALS['TL_LANG']['MSC']['translationErrorSource'];
                $this->Template->headline = $this->Template->source . '<div style="white-space:pre;overflow:scroll;font-family:Courier New"><br><br>' . str_replace("\t", '    ', htmlspecialchars(file_get_contents(TL_ROOT . '/system/modules/' . $arrSession['module']. '/languages/en/' . $arrSession['file']), ENT_COMPAT, 'UTF-8')) . '</div>';
            }
            elseif (!is_array($this->Template->translation))
            {
                $this->Template->edit = false;
                $this->Template->error = $GLOBALS['TL_LANG']['MSC']['translationError'];
                $this->Template->headline = $this->Template->translation . '<div style="white-space:pre;overflow:scroll;font-family:Courier New"><br><br>' . str_replace("\t", '    ', htmlspecialchars(file_get_contents(TL_ROOT . '/system/modules/' . $arrSession['module']. '/languages/' . $this->User->translation . '/' . $arrSession['file']), ENT_COMPAT, 'UTF-8')) . '</div>';
            }
        }

        $this->Template->modules = $arrModules;
        $this->Template->moduleClass = strlen($arrSession['module']) ? ' active' : '';
        $this->Template->files = $arrFiles;
        $this->Template->fileClass = $this->Template->edit ? ' active' : '';

        $this->Template->downloadHref = $this->addToUrl('act=download');
        $this->Template->downloadTitle = 'Download language files for this module';
        $this->Template->downloadLabel = 'Download';
    }


    /**
     * Export a combined translation file
     */
    protected function export()
    {
        $arrSession = $this->Session->get('filter_translation');
        $arrSession = $arrSession['isotope_translation'];

        $strFolder = 'system/modules/' . $arrSession['module'] . '/languages/' . $this->User->translation;
        $strZip = 'system/html/' . $this->User->translation . '.zip';
        $arrFiles = scan(TL_ROOT . '/' . $strFolder);
        $strHeader = $this->getHeader();

        $objZip = new ZipWriter($strZip);

        foreach( $arrFiles as $file )
        {
            $strData = '';

            $arrSource = $this->parseFile(TL_ROOT . '/system/modules/' . $arrSession['module']. '/languages/en/' . $file);
            $arrTranslation = array_merge($this->parseFile(TL_ROOT . '/system/modules/' . $arrSession['module']. '/languages/' . $this->User->translation . '/' . $file), $this->parseFile(TL_ROOT . '/system/modules/' . $arrSession['module']. '/languages/' . $this->User->translation . '/local/' . $file));

            foreach( array_keys($arrSource) as $key )
            {
                $value = str_replace(array("\r\n", "\n", "\r", '\n'), '\n', $arrTranslation[$key], $count);

                $strData .= $key . ' = ' . ($count > 0 ? ('"' . str_replace('"', '\"', $value) . '";'."\n") : ("'" . str_replace("'", "\'", $value) . "';\n"));
            }

            $objZip->addString($strHeader . $strData . "\n", $strFolder . '/' . $file);
        }

        $objZip->close();

        $objFile = new File($strZip);

        // Open the "save as …" dialogue
        header('Content-Type: ' . $objFile->mime);
        header('Content-Transfer-Encoding: binary');
        header('Content-Disposition: attachment; filename="' . $objFile->basename . '"');
        header('Content-Length: ' . $objFile->filesize);
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Expires: 0');

        $resFile = fopen(TL_ROOT . '/' . $strZip, 'rb');
        fpassthru($resFile);
        fclose($resFile);

        unlink($strZip);

        // Stop script
        exit;
    }


    /**
     * Return the header for a language file
     * @return string
     */
    private function getHeader()
    {
        $strHeader = "<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *";

         $objAuthors = $this->Database->prepare("SELECT * FROM tl_user WHERE translation=?")->execute($this->User->translation);

        while( $objAuthors->next() )
        {
            $strHeader .= '
 * @author     ' . $objAuthors->name . ' <' . $objAuthors->email . '>';
        }

        $strHeader .= '
 */

';

        return $strHeader;
    }


    private function parseFile($strFile)
    {
        $return = array();

        if (!is_file($strFile))
        {
            return array();
        }

        $data = file($strFile);

        return $this->parse($data);
    }


    private function parse($data)
    {
        $arrVariables = array();

        foreach ($data as $i => $line)
        {
            // Unset comments and empty lines
            if ($i == 0 || preg_match('@^/\*| \*|\*/|//@i', $line) || !strlen(trim($line)))
            {
                continue;
            }

            // Save language variable
            if(preg_match('@\$GLOBALS(\[.*?\])*@', $line, $match))
            {
                $strKey = $match[0];
            }
            else
            {
                return 'Line ' . ++$i . ': ' . $line;
            }

            if (eval($line) === false)
            {
                return 'Line ' . ++$i . ': ' . $line;
            }

            $varValue = eval('return '.$strKey.';');

            $this->parseVar($varValue, $strKey, $arrVariables);
        }

        return $arrVariables;
    }


    private function parseVar($varValue, $strKey, &$arrVariables)
    {
        if (is_array($varValue))
        {
            foreach( $varValue as $k => $v )
            {
                $this->parseVar($v, $strKey.'['.$k.']', $arrVariables);
            }

            return;
        }

        $arrVariables[$strKey] = $varValue;
    }
}

<?php

namespace Isotope\CoreBundle\DataContainer;

use Contao\ArrayUtil;
use Contao\Backend;
use Contao\BackendTemplate;
use Contao\BackendUser;
use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Exception\InternalServerErrorException;
use Contao\CoreBundle\Exception\NotFoundException;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\CoreBundle\Security\DataContainer\CreateAction;
use Contao\CoreBundle\Security\DataContainer\ReadAction;
use Contao\CoreBundle\Security\DataContainer\UpdateAction;
use Contao\Database;
use Contao\DataContainer;
use Contao\Date;
use Contao\DC_Table;
use Contao\Environment;
use Contao\FilesModel;
use Contao\Image;
use Contao\Input;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use Contao\Widget;
use Doctrine\DBAL\Exception\DriverException;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\String\UnicodeString;

class ProductData extends DC_Table
{
	/**
	 * True if we are editing a language
	 */
	protected $blnEditLanguage;

	/**
	 * Array of translations for this product's type
	 * @var array
	 */
	protected $arrTranslations;

	/**
	 * Array of language labels
	 * @var array
	 */
	protected $arrLanguageLabels;

	/**
	 * Initialize the object
	 * @param string
	 */
	public function __construct($strTable)
	{
		$GLOBALS['TL_DCA'][$strTable]['config']['ptable'] = $strTable;

		parent::__construct($strTable);

		unset($GLOBALS['TL_DCA'][$strTable]['config']['ptable']);

		if (null !== $this->currentPid)
		{
			$GLOBALS['TL_DCA'][$strTable]['config']['ptable'] = $strTable;
            $GLOBALS['TL_DCA'][$strTable]['list']['sorting']['mode'] = DataContainer::MODE_PARENT;
		}
		else
		{
			$GLOBALS['TL_DCA'][$strTable]['config']['notSortable'] = true;

            if (\in_array(Input::get('act'), ['copy', 'copyAll']))
            {
                Input::setGet('children', '1');
            }
		}

		// Allow to customize languages via the onload_callback
		if (isset($GLOBALS['TL_DCA'][$this->strTable]['config']['languages'])) {
			$arrPageLanguages = $GLOBALS['TL_DCA'][$this->strTable]['config']['languages'];
		} else {
			$arrPageLanguages = array_map(
				function ($strLang) {
					return str_replace('-', '_', $strLang);
				},
				$this->Database
					->execute("SELECT DISTINCT language FROM tl_page WHERE type='root' AND language!=''")
					->fetchEach('language')
			);
		}

		if (\count($arrPageLanguages) > 1) {
			$this->arrTranslationLabels = System::getContainer()->get('contao.intl.locales')->getLocales(null, true);
			$this->arrTranslations      = array_intersect(array_keys($this->arrTranslationLabels), $arrPageLanguages);
		}
	}

	public function __get($strKey)
	{
		switch ($strKey) {
			case 'editLanguage':
				return $this->blnEditLanguage;
		}

		return parent::__get($strKey);
	}

	/**
	 * List all records of a particular table
	 *
	 * @return string
	 */
	public function showAll()
	{
		$return = '';
		$this->limit = '';

		$objSession = System::getContainer()->get('request_stack')->getSession();

		$this->reviseTable();

		// Add to clipboard
		if (Input::get('act') == 'paste')
		{
			$this->denyAccessUnlessGranted(...$this->getClipboardPermission(Input::get('mode'), (int) Input::get('id')));

			$children = Input::get('children');

			// Backwards compatibility
			if (Input::get('childs') !== null)
			{
				trigger_deprecation('contao/core-bundle', '5.3', 'Using the "childs" query parameter has been deprecated and will no longer work in Contao 6. Use the "children" parameter instead.');
				$children = Input::get('childs');
			}

			$arrClipboard = $objSession->get('CLIPBOARD');

			$arrClipboard[$this->strTable] = array
			(
				'id' => Input::get('id'),
				'childs' => $children, // backwards compatibility
				'children' => $children,
				'mode' => Input::get('mode')
			);

			$objSession->set('CLIPBOARD', $arrClipboard);

			if ($this->currentPid)
			{
				$this->redirect(Backend::addToUrl('id=' . $this->currentPid, false, array('act', 'mode')));
			}
			else
			{
				$this->redirect(Backend::addToUrl('', false, array('act', 'mode', 'id')));
			}
		}

		// Do not show the language records
		$this->procedure[] = "language=''";

		// Custom filter
		if (!empty($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['filter']) && \is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['filter']))
		{
			foreach ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['filter'] as $filter)
			{
				if (\is_string($filter))
				{
					$this->procedure[] = $filter;
				}
				else
				{
					$this->procedure[] = $filter[0];
					$this->values[] = $filter[1];
				}
			}
		}

		$return .= $this->panel();
		$return .= ($this->intId && (Input::get('pid') === null || (Input::get('pid') != '' && (int) Input::get('pid') != 0))) ? $this->parentView() : $this->listView();

		return $return;
	}

	/**
	 * Duplicate all child records of a duplicated record
	 *
	 * @param string  $table
	 * @param integer $insertID
	 * @param integer $id
	 * @param integer $parentId
	 */
	protected function copyChildren($table, $insertID, $id, $parentId)
	{
		$time = time();
		$copy = array();
		$cctable = array();
		$ctable = $GLOBALS['TL_DCA'][$table]['config']['ctable'] ?? array();
		$db = Database::getInstance();
		$children = Input::get('children');

		// Backwards compatibility
		if (Input::get('childs') !== null)
		{
			trigger_deprecation('contao/core-bundle', '5.3', 'Using the "childs" query parameter has been deprecated and will no longer work in Contao 6. Use the "children" parameter instead.');
			$children = Input::get('childs');
		}

		/** PATCH: removed check for sorting field */
		if (!($GLOBALS['TL_DCA'][$table]['config']['ptable'] ?? null) && $children && $db->fieldExists('pid', $table))
		{
			$ctable[] = $table;
		}

		if (empty($ctable) || !\is_array($ctable))
		{
			return;
		}

		// Walk through each child table
		foreach ($ctable as $v)
		{
			$this->loadDataContainer($v);
			$cctable[$v] = $GLOBALS['TL_DCA'][$v]['config']['ctable'] ?? null;

			if (!($GLOBALS['TL_DCA'][$v]['config']['doNotCopyRecords'] ?? null))
			{
				// Consider the dynamic parent table (see #4867)
				if ($GLOBALS['TL_DCA'][$v]['config']['dynamicPtable'] ?? null)
				{
					$objCTable = $db
						->prepare("SELECT * FROM $v WHERE pid=? AND ptable=?" . ($db->fieldExists('sorting', $v) ? " ORDER BY sorting, id" : ""))
						->execute($id, $table);
				}
				else
				{
					$objCTable = $db
						->prepare("SELECT * FROM $v WHERE pid=?" . ($db->fieldExists('sorting', $v) ? " ORDER BY sorting, id" : ""))
						->execute($id);
				}

				while ($objCTable->next())
				{
					// Exclude the duplicated record itself
					if ($v == $table && $objCTable->id == $parentId)
					{
						continue;
					}

					foreach ($objCTable->row() as $kk=>$vv)
					{
						if ($kk == 'id')
						{
							continue;
						}

						// Never copy passwords
						if (($GLOBALS['TL_DCA'][$v]['fields'][$kk]['inputType'] ?? null) == 'password')
						{
							$vv = Widget::getEmptyValueByFieldType($GLOBALS['TL_DCA'][$v]['fields'][$kk]['sql'] ?? array());
						}

						// Empty unique fields or add a unique identifier in copyAll mode
						elseif ($GLOBALS['TL_DCA'][$v]['fields'][$kk]['eval']['unique'] ?? null)
						{
							$vv = (Input::get('act') == 'copyAll' && !$GLOBALS['TL_DCA'][$v]['fields'][$kk]['eval']['doNotCopy']) ? $vv . '-' . substr(md5(uniqid(mt_rand(), true)), 0, 8) : Widget::getEmptyValueByFieldType($GLOBALS['TL_DCA'][$v]['fields'][$kk]['sql'] ?? array());
						}

						// Reset doNotCopy and fallback fields to their default value
						elseif (($GLOBALS['TL_DCA'][$v]['fields'][$kk]['eval']['doNotCopy'] ?? null) || ($GLOBALS['TL_DCA'][$v]['fields'][$kk]['eval']['fallback'] ?? null))
						{
							$vv = Widget::getEmptyValueByFieldType($GLOBALS['TL_DCA'][$v]['fields'][$kk]['sql'] ?? array());

							// Use array_key_exists to allow NULL (see #5252)
							if (\array_key_exists('default', $GLOBALS['TL_DCA'][$v]['fields'][$kk] ?? array()))
							{
								$default = $GLOBALS['TL_DCA'][$v]['fields'][$kk]['default'];

								if ($default instanceof \Closure)
								{
									$default = $default($this);
								}

								$vv = \is_array($default) ? serialize($default) : $default;
							}
						}

						$copy[$v][$objCTable->id][$kk] = $vv;
					}

					$copy[$v][$objCTable->id]['pid'] = $insertID;
					$copy[$v][$objCTable->id]['tstamp'] = $time;
				}
			}
		}

		// Duplicate the child records
		foreach ($copy as $k=>$v)
		{
			if (!empty($v))
			{
				foreach ($v as $kk=>$vv)
				{
					$objInsertStmt = $db->prepare("INSERT INTO " . $k . " %s")->set($vv)->execute();

					if ($objInsertStmt->affectedRows)
					{
						$insertID = $objInsertStmt->insertId;

						if ($kk != $parentId && (!empty($cctable[$k]) || ($GLOBALS['TL_DCA'][$k]['list']['sorting']['mode'] ?? null) == self::MODE_TREE))
						{
							$this->copyChildren($k, $insertID, $kk, $parentId);
						}

						if (\is_array($GLOBALS['TL_DCA'][$k]['config']['oncopy_callback'] ?? null))
						{
							foreach ($GLOBALS['TL_DCA'][$k]['config']['oncopy_callback'] as $callback)
							{
								$dc = (new \ReflectionClass(self::class))->newInstanceWithoutConstructor();
								$dc->table = $k;
								$dc->id = $kk;

								if (\is_array($callback))
								{
									System::importStatic($callback[0])->{$callback[1]}($insertID, $dc);
								}
								elseif (\is_callable($callback))
								{
									$callback($insertID, $dc);
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Auto-generate a form to edit the current database record
	 *
	 * @param integer $intId
	 * @param integer $ajaxId
	 *
	 * @return string
	 *
	 * @throws AccessDeniedException
	 * @throws InternalServerErrorException
	 */
	public function edit($intId=null, $ajaxId=null)
	{
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'] ?? null)
		{
			throw new AccessDeniedException('Table "' . $this->strTable . '" is not editable.');
		}

		if ($intId)
		{
			$this->intId = $intId;
		}

		// Get the current record
		$currentRecord = $this->getCurrentRecord();

		// Redirect if there is no record with the given ID
		if (null === $currentRecord)
		{
			throw new NotFoundException('Cannot load record "' . $this->strTable . '.id=' . $this->intId . '".');
		}

		$this->denyAccessUnlessGranted(ContaoCorePermissions::DC_PREFIX . $this->strTable, new UpdateAction($this->strTable, $currentRecord));

		// ID of a language record is not allowed
		if (!empty($currentRecord['language']))
		{
			throw new AccessDeniedException('Cannot edit language record "'.$this->strTable.'.id='.$this->intId.'"');
		}

		// Store the active record (backwards compatibility)
		$this->objActiveRecord = (object) $currentRecord;

		$return = '';
		$this->values[] = $this->intId;
		$this->procedure[] = 'id=?';
		$this->arrSubmit = array();
		$this->blnCreateNewVersion = false;
		$objVersions = new Versions($this->strTable, $this->intId);

		if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['hideVersionMenu'] ?? null))
		{
			// Compare versions
			if (Input::get('versions'))
			{
				$objVersions->compare();
			}

			// Restore a version
			if (Input::post('FORM_SUBMIT') == 'tl_version' && Input::post('version'))
			{
				$objVersions->restore(Input::post('version'));

				$this->invalidateCacheTags();

				$this->reload();
			}
		}

		$objVersions->initialize();
		$intLatestVersion = $objVersions->getLatestVersion();

		$security = System::getContainer()->get('security.helper');

		// Load and/or change language
		$this->blnEditLanguage = false;

		if (!empty($this->arrTranslations))
		{
			$blnLanguageUpdated = false;

			/** @var AttributeBagInterface $sessionBag */
			$sessionBag = System::getContainer()->get('request_stack')->getSession()->getBag('contao_backend');
			$session = $sessionBag->all();

			if (Input::post('FORM_SUBMIT') === 'tl_language')
			{
				if (\in_array(Input::post('language'), $this->arrTranslations))
				{
					$session['language'][$this->strTable][$this->intId] = Input::post('language');
				}
				else
				{
					unset($session['language'][$this->strTable][$this->intId]);
				}

				$blnLanguageUpdated = true;
			}
			elseif (Input::post('FORM_SUBMIT') == $this->strTable && isset($_POST['deleteLanguage']))
			{
				$this->Database->prepare("DELETE FROM {$this->strTable} WHERE pid=? AND language=?")->execute($this->intId, $session['language'][$this->strTable][$this->intId]);
				unset($session['language'][$this->strTable][$this->intId]);
				$blnLanguageUpdated = true;
			}

			if ($blnLanguageUpdated)
			{
				$sessionBag->replace($session);
				unset($_SESSION['TL_INFO']);
				\Contao\Controller::reload();
			}

			if (($_SESSION['BE_DATA']['language'][$this->strTable][$this->intId] ?? null) != '' && \in_array($_SESSION['BE_DATA']['language'][$this->strTable][$this->intId], $this->arrTranslations))
			{
				$objRow = $this->Database->prepare("SELECT * FROM {$this->strTable} WHERE pid=? AND language=?")->execute($this->intId, $_SESSION['BE_DATA']['language'][$this->strTable][$this->intId]);

				if (!$objRow->numRows)
				{
					$intLanguage = $this->Database->prepare("INSERT INTO {$this->strTable} (pid,tstamp,language) VALUES (?,?,?)")->execute($this->intId, time(), $_SESSION['BE_DATA']['language'][$this->strTable][$this->intId])->insertId;

					$objRow = $this->Database->prepare("SELECT * FROM {$this->strTable} WHERE id=?")->execute($intLanguage);
				}

				$this->objActiveRecord = $objRow;
				$this->values = array($this->intId, $_SESSION['BE_DATA']['language'][$this->strTable][$this->intId]);
				$this->procedure = array('pid=?', 'language=?');
				$this->blnEditLanguage = true;
			}
		}

		// Build an array from boxes and rows
		$this->strPalette = $this->getPalette();
		$boxes = StringUtil::trimsplit(';', $this->strPalette);
		$legends = array();

		if (!empty($boxes))
		{
			foreach ($boxes as $k=>$v)
			{
				$eCount = 1;
				$boxes[$k] = StringUtil::trimsplit(',', $v);

				foreach ($boxes[$k] as $kk=>$vv)
				{
					if (preg_match('/^\[.*]$/', $vv))
					{
						++$eCount;
						continue;
					}

					if (preg_match('/^{.*}$/', $vv))
					{
						$legends[$k] = substr($vv, 1, -1);
						unset($boxes[$k][$kk]);
					}
					elseif (!\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$vv] ?? null) || (DataContainer::isFieldExcluded($this->strTable, $vv) && !$security->isGranted(ContaoCorePermissions::USER_CAN_EDIT_FIELD_OF_TABLE, $this->strTable . '::' . $vv)))
					{
						unset($boxes[$k][$kk]);
					}
					elseif ($this->blnEditLanguage && !($GLOBALS['TL_DCA'][$this->strTable]['fields'][$vv]['eval']['multilingual'] ?? null))
					{
						unset($boxes[$k][$kk]);
					}
				}

				// Unset a box if it does not contain any fields
				if (\count($boxes[$k]) < $eCount)
				{
					unset($boxes[$k]);
				}
			}

			/** @var AttributeBagInterface $objSessionBag */
			$objSessionBag = System::getContainer()->get('request_stack')->getSession()->getBag('contao_backend');

			$class = 'tl_tbox';
			$fs = $objSessionBag->get('fieldset_states');

			// Render boxes
			foreach ($boxes as $k=>$v)
			{
				$arrAjax = array();
				$blnAjax = false;
				$key = '';
				$cls = '';
				$legend = '';

				if (isset($legends[$k]))
				{
					list($key, $cls) = explode(':', $legends[$k]) + array(null, null);

					$legend = "\n" . '<legend><button type="button" data-action="contao--toggle-fieldset#toggle">' . ($GLOBALS['TL_LANG'][$this->strTable][$key] ?? $key) . '</button></legend>';
				}

				if ($legend)
				{
					if (isset($fs[$this->strTable][$key]))
					{
						$class .= ($fs[$this->strTable][$key] ? '' : ' collapsed');
					}
					elseif ($cls)
					{
						// Convert the ":hide" suffix from the DCA
						if ($cls == 'hide')
						{
							$cls = 'collapsed';
						}

						$class .= ' ' . $cls;
					}
				}

				$return .= "\n\n" . '<fieldset class="' . $class . ($legend ? '' : ' nolegend') . '" data-controller="contao--toggle-fieldset" data-contao--toggle-fieldset-id-value="' . $key . '" data-contao--toggle-fieldset-table-value="' . $this->strTable . '" data-contao--toggle-fieldset-collapsed-class="collapsed" data-contao--jump-targets-target="section" data-contao--jump-targets-label-value="' . ($GLOBALS['TL_LANG'][$this->strTable][$key] ?? $key) . '" data-action="contao--jump-targets:scrollto->contao--toggle-fieldset#open">' . $legend;
				$thisId = '';

				// Build rows of the current box
				foreach ($v as $vv)
				{
					if ($vv == '[EOF]')
					{
						if ($blnAjax && Environment::get('isAjaxRequest'))
						{
							if ($ajaxId == $thisId)
							{
								if (($intLatestVersion = $objVersions->getLatestVersion()) !== null)
								{
									$arrAjax[$thisId] .= '<input type="hidden" name="VERSION_NUMBER" value="' . $intLatestVersion . '">';
								}

								return $arrAjax[$thisId];
							}

							if (\count($arrAjax) > 1)
							{
								$current = "\n" . '<div id="' . $thisId . '" class="subpal cf">' . $arrAjax[$thisId] . '</div>';
								unset($arrAjax[$thisId]);
								end($arrAjax);
								$thisId = key($arrAjax);
								$arrAjax[$thisId] .= $current;
							}
						}

						$return .= "\n" . '</div>';

						continue;
					}

					if (preg_match('/^\[.*]$/', $vv))
					{
						$thisId = 'sub_' . substr($vv, 1, -1);
						$arrAjax[$thisId] = '';
						$blnAjax = ($ajaxId == $thisId && Environment::get('isAjaxRequest')) ? true : $blnAjax;
						$return .= "\n" . '<div id="' . $thisId . '" class="subpal cf">';

						continue;
					}

					$this->strField = $vv;
					$this->strInputName = $vv;
					$this->varValue = $currentRecord[$vv] ?? null;

					// Convert CSV fields (see #2890)
					if (($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['multiple'] ?? null) && isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv']))
					{
						$this->varValue = StringUtil::trimsplit($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv'], $this->varValue);
					}

					// Call load_callback
					if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback'] ?? null))
					{
						foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback'] as $callback)
						{
							if (\is_array($callback))
							{
								$this->varValue = System::importStatic($callback[0])->{$callback[1]}($this->varValue, $this);
							}
							elseif (\is_callable($callback))
							{
								$this->varValue = $callback($this->varValue, $this);
							}
						}
					}

					// Re-set the current value
					$this->objActiveRecord->{$this->strField} = $this->varValue;

					// Build the row and pass the current palette string (thanks to Tristan Lins)
					$blnAjax ? $arrAjax[$thisId] .= $this->row() : $return .= $this->row();
				}

				$class = 'tl_box';
				$return .= "\n" . '</fieldset>';
			}

			$this->submit();
		}

		// Reload the page to prevent _POST variables from being sent twice
		if (!$this->noReload && Input::post('FORM_SUBMIT') == $this->strTable)
		{
			// Show a warning if the record has been saved by another user (see #8412)
			if ($intLatestVersion !== null && Input::post('VERSION_NUMBER') !== null && $intLatestVersion > Input::post('VERSION_NUMBER'))
			{
				$objTemplate = new BackendTemplate('be_conflict');
				$objTemplate->language = $GLOBALS['TL_LANGUAGE'];
				$objTemplate->title = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['versionConflict']);
				$objTemplate->theme = Backend::getTheme();
				$objTemplate->charset = System::getContainer()->getParameter('kernel.charset');
				$objTemplate->h1 = $GLOBALS['TL_LANG']['MSC']['versionConflict'];
				$objTemplate->explain1 = \sprintf($GLOBALS['TL_LANG']['MSC']['versionConflict1'], $intLatestVersion, Input::post('VERSION_NUMBER'));
				$objTemplate->explain2 = \sprintf($GLOBALS['TL_LANG']['MSC']['versionConflict2'], $intLatestVersion + 1, $intLatestVersion);
				$objTemplate->diff = $objVersions->compare(true);
				$objTemplate->href = Environment::get('requestUri');
				$objTemplate->button = $GLOBALS['TL_LANG']['MSC']['continue'];

				throw new ResponseException($objTemplate->getResponse());
			}

			// Redirect
			if (Input::post('saveNclose') !== null)
			{
				Message::reset();

				$this->redirect($this->getReferer());
			}
			elseif (Input::post('saveNedit') !== null)
			{
				Message::reset();

				$this->redirect($this->addToUrl($GLOBALS['TL_DCA'][$this->strTable]['list']['operations']['children']['href'] ?? '', false, array('s2e', 'act', 'mode', 'pid')));
			}
			elseif (Input::post('saveNback') !== null)
			{
				Message::reset();

				if (!$this->ptable)
				{
					$this->redirect(System::getContainer()->get('router')->generate('contao_backend') . '?do=' . Input::get('do'));
				}
				// TODO: try to abstract this
				elseif ($this->ptable == 'tl_page' && $this->strTable == 'tl_article')
				{
					$this->redirect($this->getReferer(false, $this->strTable));
				}
				else
				{
					$this->redirect($this->getReferer(false, $this->ptable));
				}
			}
			elseif (Input::post('saveNcreate') !== null)
			{
				Message::reset();

				$strUrl = System::getContainer()->get('router')->generate('contao_backend') . '?do=' . Input::get('do');

				if (Input::get('table') !== null)
				{
					$strUrl .= '&amp;table=' . Input::get('table');
				}

				// Tree view
				if ($this->treeView)
				{
					$strUrl .= '&amp;act=create&amp;mode=1&amp;pid=' . $this->intId;
				}

				// Parent view
				// PATCH: check for parent ID instead of sorting mode
				elseif ($this->activeRecord->pid > 0)
				{
					$strUrl .= Database::getInstance()->fieldExists('sorting', $this->strTable) ? '&amp;act=create&amp;mode=1&amp;pid=' . $this->intId . '&amp;id=' . ($currentRecord['pid'] ?? null) : '&amp;act=create&amp;mode=2&amp;pid=' . ($currentRecord['pid'] ?? null);
				}

				// List view
				else
				{
					$strUrl .= $this->ptable ? '&amp;act=create&amp;mode=2&amp;pid=' . $this->intCurrentPid : '&amp;act=create';
				}

				$this->redirect($strUrl . '&amp;rt=' . System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue());
			}
			elseif (Input::post('saveNduplicate') !== null)
			{
				Message::reset();

				$strUrl = System::getContainer()->get('router')->generate('contao_backend') . '?do=' . Input::get('do');

				if (Input::get('table') !== null)
				{
					$strUrl .= '&amp;table=' . Input::get('table');
				}

				// Tree view
				if ($this->treeView)
				{
					$strUrl .= '&amp;act=copy&amp;mode=1&amp;id=' . $this->intId . '&amp;pid=' . $this->intId;
				}

				// Parent view
				elseif (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) == self::MODE_PARENT)
				{
					$strUrl .= Database::getInstance()->fieldExists('sorting', $this->strTable) ? '&amp;act=copy&amp;mode=1&amp;pid=' . $this->intId . '&amp;id=' . $this->intId : '&amp;act=copy&amp;mode=2&amp;pid=' . $this->intCurrentPid . '&amp;id=' . $this->intId;

					if (($currentRecord['ptable'] ?? null) === $this->strTable)
					{
						$strUrl .= '&amp;ptable=' . $currentRecord['ptable'];
					}
				}

				// List view
				else
				{
					$strUrl .= $this->ptable ? '&amp;act=copy&amp;mode=2&amp;pid=' . $this->intCurrentPid . '&amp;id=' . $this->intId : '&amp;act=copy&amp;id=' . $this->intId;
				}

				$this->redirect($strUrl . '&amp;rt=' . System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue());
			}

			$this->reload();
		}

		// Versions overview
		if (($GLOBALS['TL_DCA'][$this->strTable]['config']['enableVersioning'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['config']['hideVersionMenu'] ?? null))
		{
			$version = $objVersions->renderDropdown();
		}
		else
		{
			$version = '';
		}

		if ('' === $version)
		{
			$version = '<div class="tl_version_panel"></div>';
		}

		// Check languages
		if (!empty($this->arrTranslations))
		{
			$arrAvailableLanguages = $this->Database->prepare("SELECT language FROM {$this->strTable} WHERE pid=?")->execute($this->intId)->fetchEach('language');
			$available = '';
			$undefined = '';

			foreach ($this->arrTranslations as $language)
			{
				if (\in_array($language, $arrAvailableLanguages))
				{
					if (($_SESSION['BE_DATA']['language'][$this->strTable][$this->intId] ?? null) == $language)
					{
						$available .= '<option value="' . $language . '" selected="selected">' . $this->arrTranslationLabels[$language] . '</option>';
						$_SESSION['TL_INFO'] = array($GLOBALS['TL_LANG']['MSC']['editingLanguage']);
					}
					else
					{
						$available .= '<option value="' . $language . '">' . $this->arrTranslationLabels[$language] . '</option>';
					}
				}
				else
				{
					$undefined .= '<option value="' . $language . '">' . $this->arrTranslationLabels[$language] . ' (' . $GLOBALS['TL_LANG']['MSC']['undefinedLanguage'] . ')' . '</option>';
				}
			}

			$version = str_replace(
				'<div class="tl_version_panel">',
				'<div class="tl_version_panel tl_iso_products_panel">
<form action="' . \Contao\StringUtil::ampersand(Environment::get('request'), true) . '" id="tl_language" class="tl_form" method="post">
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_language">
<input type="hidden" name="REQUEST_TOKEN" value="' . \Contao\System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue() . '">
<select name="language" class="tl_select' . (!empty($_SESSION['BE_DATA']['language'][$this->strTable][$this->intId]) ? ' active' : '') . '" onchange="document.id(this).getParent(\'form\').submit()">
	<option value="">' . $GLOBALS['TL_LANG']['MSC']['defaultLanguage'] . '</option>' . $available . $undefined . '
</select>
<noscript>
<input type="submit" name="editLanguage" class="tl_submit" value="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['editLanguage']) . '">
</noscript>
</div>
</form>',
				$version
			);
		}

		// Submit buttons
		$arrButtons = array();
		$arrButtons['save'] = '<button type="submit" name="save" id="save" class="tl_submit" accesskey="s">' . $GLOBALS['TL_LANG']['MSC']['save'] . '</button>';
		$deleteLanguageButton = '';

		if (!Input::get('nb'))
		{
			$arrButtons['saveNclose'] = '<button type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c" data-action="contao--scroll-offset#discard">' . $GLOBALS['TL_LANG']['MSC']['saveNclose'] . '</button>';

			if (!Input::get('nc'))
			{
				if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable'] ?? null) && $security->isGranted(ContaoCorePermissions::DC_PREFIX . $this->strTable, new CreateAction($this->strTable, array('pid' => $this->intCurrentPid))))
				{
					$arrButtons['saveNcreate'] = '<button type="submit" name="saveNcreate" id="saveNcreate" class="tl_submit" accesskey="n" data-action="contao--scroll-offset#discard">' . $GLOBALS['TL_LANG']['MSC']['saveNcreate'] . '</button>';
				}

				if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['config']['notCopyable'] ?? null) && $security->isGranted(ContaoCorePermissions::DC_PREFIX . $this->strTable, new CreateAction($this->strTable, array_replace($currentRecord, array('id' => null, 'sorting' => null)))))
				{
					$arrButtons['saveNduplicate'] = '<button type="submit" name="saveNduplicate" id="saveNduplicate" class="tl_submit" accesskey="d" data-action="contao--scroll-offset#discard">' . $GLOBALS['TL_LANG']['MSC']['saveNduplicate'] . '</button>';
				}

				if ($GLOBALS['TL_DCA'][$this->strTable]['config']['switchToEdit'] ?? null)
				{
					$arrButtons['saveNedit'] = '<button type="submit" name="saveNedit" id="saveNedit" class="tl_submit" accesskey="e" data-action="contao--scroll-offset#discard">' . $GLOBALS['TL_LANG']['MSC']['saveNedit'] . '</button>';
				}

				if ($this->ptable || ($GLOBALS['TL_DCA'][$this->strTable]['config']['switchToEdit'] ?? null) || ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) == self::MODE_PARENT)
				{
					$arrButtons['saveNback'] = '<button type="submit" name="saveNback" id="saveNback" class="tl_submit" accesskey="g" data-action="contao--scroll-offset#discard">' . $GLOBALS['TL_LANG']['MSC']['saveNback'] . '</button>';
				}

				if ($this->blnEditLanguage)
				{
					$deleteLanguageButton = '<button type="submit" name="deleteLanguage" class="tl_submit" style="float:right" onclick="return confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteLanguageConfirm'] . '\')">' . $GLOBALS['TL_LANG']['MSC']['deleteLanguage'] . '</button>';
				}
			}
		}

		// Call the buttons_callback (see #4691)
		if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback'] ?? null))
		{
			foreach ($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback'] as $callback)
			{
				if (\is_array($callback))
				{
					$arrButtons = System::importStatic($callback[0])->{$callback[1]}($arrButtons, $this);
				}
				elseif (\is_callable($callback))
				{
					$arrButtons = $callback($arrButtons, $this);
				}
			}
		}

		if (\count($arrButtons) < 3)
		{
			$strButtons = implode(' ', $arrButtons);
		}
		else
		{
			$strButtons = array_shift($arrButtons) . ' ';
			$strButtons .= '<div class="split-button">';
			$strButtons .= array_shift($arrButtons) . '<button type="button" id="sbtog">' . Image::getHtml('navcol.svg') . '</button> <ul class="invisible">';

			foreach ($arrButtons as $strButton)
			{
				$strButtons .= '<li>' . $strButton . '</li>';
			}

			$strButtons .= '</ul></div>'.$deleteLanguageButton;
		}

		// Add the buttons and end the form
		$return .= '
</div>
<div class="tl_formbody_submit">
<div class="tl_submit_container">
  ' . $strButtons . '
</div>
</div>
</form>';

		$strVersionField = '';

		// Store the current version number (see #8412)
		if ($intLatestVersion !== null)
		{
			$strVersionField = '
<input type="hidden" name="VERSION_NUMBER" value="' . $intLatestVersion . '">';
		}

		$strBackUrl = $this->getReferer(true);

		if ((string) $currentRecord['tstamp'] === '0')
		{
			$strBackUrl = preg_replace('/&(?:amp;)?revise=[^&]+|$/', '&amp;revise=' . $this->strTable . '.' . ((int) $this->intId), $strBackUrl, 1);

			$return .= '
<script>
  history.pushState({}, "");
  window.addEventListener("popstate", () => fetch(document.querySelector(".header_back").href).then(() => history.back()));
</script>';
		}

		$copyFallback = $this->blnEditLanguage ? '&nbsp;&nbsp;::&nbsp;&nbsp;<a href="' . Backend::addToUrl('act=copyFallback') . '" class="header_iso_copy" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['copyFallback'] ?? '') . '" accesskey="d" onclick="Backend.getScrollOffset();">' . ($GLOBALS['TL_LANG']['MSC']['copyFallback'] ?? 'copyFallback') . '</a>' : '';

		// Begin the form (-> DO NOT CHANGE THIS ORDER -> this way the onsubmit attribute of the form can be changed by a field)
		$return = $version . Message::generate() . ($this->noReload ? '
<p class="tl_error">' . $GLOBALS['TL_LANG']['ERR']['submit'] . '</p>' : '') . '
<div id="tl_buttons">' . (Input::get('nb') ? '' : '
<a href="' . $strBackUrl . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b" data-action="contao--scroll-offset#discard">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>') . $copyFallback . '
</div>' . '
<form id="' . $this->strTable . '" class="tl_form tl_edit_form" method="post" enctype="' . ($this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded') . '"' . (!empty($this->onsubmit) ? ' onsubmit="' . implode(' ', $this->onsubmit) . '"' : '') . '>
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="' . $this->strTable . '">
<input type="hidden" name="REQUEST_TOKEN" value="' . htmlspecialchars(System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue(), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5) . '">' . $strVersionField . $return;

		// Set the focus if there is an error
		if ($this->noReload)
		{
			$return .= '
<script>
  window.addEvent(\'domready\', function() {
	var error = $(\'' . $this->strTable . '\').getElement(\'label.error\');
	if (error) Backend.vScrollTo((error.getPosition().y - 20));
  });
</script>';
		}

		$return = '
<div data-controller="contao--jump-targets">
	<div class="jump-targets"><div class="inner" data-contao--jump-targets-target="navigation"></div></div>
	' . $return . '
</div>';

		return $return;
	}

	/**
	 * Auto-generate a form to override all records that are currently shown
	 *
	 * @return string
	 *
	 * @throws InternalServerErrorException
	 */
	public function overrideAll()
	{
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'] ?? null)
		{
			throw new AccessDeniedException('Table "' . $this->strTable . '" is not editable.');
		}

		$return = '';

		$objSession = System::getContainer()->get('request_stack')->getSession();

		// Get current IDs from session
		$session = $objSession->all();
		$ids = $session['CURRENT']['IDS'] ?? array();

		// Save field selection in session
		if (Input::post('FORM_SUBMIT') == $this->strTable . '_all' && Input::get('fields'))
		{
			$session['CURRENT'][$this->strTable] = Input::post('all_fields');
			$objSession->replace($session);
		}

		$db = Database::getInstance();
		$security = System::getContainer()->get('security.helper');
		$user = BackendUser::getInstance();

		// Add fields
		$fields = $session['CURRENT'][$this->strTable] ?? array();

		if (!empty($fields) && \is_array($fields) && Input::get('fields'))
		{
			$class = 'tl_tbox';
			$excludedFields = array();

			// Save record
			if (Input::post('FORM_SUBMIT') == $this->strTable)
			{
				$db->beginTransaction();

				try
				{
					static::preloadCurrentRecords($ids, $this->strTable);

				foreach ($ids as $id)
				{
						try
						{
							$currentRecord = $this->getCurrentRecord($id);

							if ($currentRecord === null)
							{
								continue;
							}
						}
						catch (AccessDeniedException)
						{
							continue;
						}

					$this->intId = $id;
					$this->procedure = array('id=?');
					$this->values = array($this->intId);
						$this->arrSubmit = array();
					$this->blnCreateNewVersion = false;

						// Store the active record (backwards compatibility)
						$this->objActiveRecord = (object) $currentRecord;

					$objVersions = new Versions($this->strTable, $this->intId);
					$objVersions->initialize();

					$this->strPalette = StringUtil::trimsplit('[;,]', $this->getPalette());

					// Store all fields
					foreach ($fields as $v)
					{
						// Check whether field is excluded or not in palette
						if (isset($excludedFields[$v]) || (DataContainer::isFieldExcluded($this->strTable, $v) && !$security->isGranted(ContaoCorePermissions::USER_CAN_EDIT_FIELD_OF_TABLE, $this->strTable . '::' . $v)) || !\in_array($v, $this->strPalette))
						{
								$excludedFields[$v] = true;

							continue;
						}

						$this->strField = $v;
						$this->strInputName = $v;
						$this->varValue = '';

						// Make sure the new value is applied
						$GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['alwaysSave'] = true;

						// Store value
						$this->row();
					}

						try
								{
							$this->submit();
								}
						catch (AccessDeniedException)
								{
							continue;
								}
							}
						}
				catch (\Throwable $e)
				{
					$db->rollbackTransaction();

					throw $e;
				}

				// Reload the page to prevent _POST variables from being sent twice
				if ($this->noReload)
						{
					$db->rollbackTransaction();
						}
						else
						{
					$db->commitTransaction();

					if (Input::post('saveNclose') !== null)
								{
						$this->redirect($this->getReferer());
									}

					$this->reload();
				}
			}

			// Begin current row
			$return .= '
<div class="' . $class . '">';

			foreach ($fields as $v)
			{
				// Check whether field is excluded
				if (isset($excludedFields[$v]) || (DataContainer::isFieldExcluded($this->strTable, $v) && !$security->isGranted(ContaoCorePermissions::USER_CAN_EDIT_FIELD_OF_TABLE, $this->strTable . '::' . $v)))
				{
					continue;
				}

				$this->intId = 0;
				$this->procedure = array('id=?');
				$this->values = array($this->intId);
				$this->strField = $v;
				$this->strInputName = $v;
				$this->varValue = '';

				// Disable auto-submit
				$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['submitOnChange'] = false;
				$return .= $this->row();
			}

			// Close box
			$return .= '
</div>';

			// Submit buttons
			$arrButtons = array();
			$arrButtons['save'] = '<button type="submit" name="save" id="save" class="tl_submit" accesskey="s">' . $GLOBALS['TL_LANG']['MSC']['save'] . '</button>';
			$arrButtons['saveNclose'] = '<button type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c" data-action="contao--scroll-offset#discard">' . $GLOBALS['TL_LANG']['MSC']['saveNclose'] . '</button>';

			// Call the buttons_callback (see #4691)
			if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback'] ?? null))
			{
				foreach ($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback'] as $callback)
				{
					if (\is_array($callback))
					{
						$arrButtons = System::importStatic($callback[0])->{$callback[1]}($arrButtons, $this);
					}
					elseif (\is_callable($callback))
					{
						$arrButtons = $callback($arrButtons, $this);
					}
				}
			}

			if (\count($arrButtons) < 3)
			{
				$strButtons = implode(' ', $arrButtons);
			}
			else
			{
				$strButtons = array_shift($arrButtons) . ' ';
				$strButtons .= '<div class="split-button">';
				$strButtons .= array_shift($arrButtons) . '<button type="button" id="sbtog">' . Image::getHtml('navcol.svg') . '</button> <ul class="invisible">';

				foreach ($arrButtons as $strButton)
				{
					$strButtons .= '<li>' . $strButton . '</li>';
				}

				$strButtons .= '</ul></div>';
			}

			// Add the form
			$return = '
<form id="' . $this->strTable . '" class="tl_form tl_edit_form" method="post" enctype="' . ($this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded') . '">
<div class="tl_formbody_edit nogrid">
<input type="hidden" name="FORM_SUBMIT" value="' . $this->strTable . '">
<input type="hidden" name="REQUEST_TOKEN" value="' . htmlspecialchars(System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue(), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5) . '">
<input type="hidden" name="IDS[]" value="' . implode('"><input type="hidden" name="IDS[]" value="', $ids) . '">' . ($this->noReload ? '
<p class="tl_error">' . $GLOBALS['TL_LANG']['ERR']['submit'] . '</p>' : '') . $return . '
</div>
<div class="tl_formbody_submit">
<div class="tl_submit_container">
  ' . $strButtons . '
</div>
</div>
</form>';

			// Set the focus if there is an error
			if ($this->noReload)
			{
				$return .= '
<script>
  window.addEvent(\'domready\', function() {
	var error = $(\'' . $this->strTable . '\').getElement(\'label.error\');
	if (error) Backend.vScrollTo((error.getPosition().y - 20));
  });
</script>';
			}
		}

		// Else show a form to select the fields
		else
		{
			$options = '';
			$fields = array();

			// Add fields of the current table
			$fields = array_merge($fields, array_keys($GLOBALS['TL_DCA'][$this->strTable]['fields'] ?? array()));

			// Add meta fields if the current user is an administrator
			if ($user->isAdmin)
			{
				if ($db->fieldExists('sorting', $this->strTable) && !\in_array('sorting', $fields))
				{
					array_unshift($fields, 'sorting');
				}

				if ($db->fieldExists('pid', $this->strTable) && !\in_array('pid', $fields))
				{
					array_unshift($fields, 'pid');
				}
			}

			// Show all non-excluded fields
			foreach ($fields as $field)
			{
				if ($field == 'pid' || $field == 'sorting' || ((!DataContainer::isFieldExcluded($this->strTable, $field) || $security->isGranted(ContaoCorePermissions::USER_CAN_EDIT_FIELD_OF_TABLE, $this->strTable . '::' . $field)) && !($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['doNotShow'] ?? null) && (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['inputType']) || \is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['input_field_callback'] ?? null) || \is_callable($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['input_field_callback'] ?? null))))
				{
					$options .= '
  <input type="checkbox" name="all_fields[]" id="all_' . $field . '" class="tl_checkbox" value="' . StringUtil::specialchars($field) . '"> <label for="all_' . $field . '" class="tl_checkbox_label">' . (($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] ?? (\is_array($GLOBALS['TL_LANG']['MSC'][$field] ?? null) ? $GLOBALS['TL_LANG']['MSC'][$field][0] : ($GLOBALS['TL_LANG']['MSC'][$field] ?? null)) ?? $field) . ' <span class="label-info">[' . $field . ']</span>') . '</label><br>';
				}
			}

			$blnIsError = Input::isPost() && !Input::post('all_fields');

			// Return the select menu
			$return .= '
<form action="' . StringUtil::ampersand(Environment::get('requestUri')) . '&amp;fields=1" id="' . $this->strTable . '_all" class="tl_form tl_edit_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="' . $this->strTable . '_all">
<input type="hidden" name="REQUEST_TOKEN" value="' . htmlspecialchars(System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue(), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5) . '">
<input type="hidden" name="IDS[]" value="' . implode('"><input type="hidden" name="IDS[]" value="', $ids) . '">' . ($blnIsError ? '
<p class="tl_error">' . $GLOBALS['TL_LANG']['ERR']['submit'] . '</p>' : '') . '
<div class="tl_tbox">
<div class="widget">
<fieldset class="tl_checkbox_container">
  <legend' . ($blnIsError ? ' class="error"' : '') . '>' . $GLOBALS['TL_LANG']['MSC']['all_fields'][0] . '<span class="mandatory">*</span></legend>
  <input type="checkbox" id="check_all" class="tl_checkbox" onclick="Backend.toggleCheckboxes(this)"> <label for="check_all" class="check-all"><em>' . $GLOBALS['TL_LANG']['MSC']['selectAll'] . '</em></label><br>' . $options . '
</fieldset>' . ($blnIsError ? '
<p class="tl_error">' . $GLOBALS['TL_LANG']['ERR']['all_fields'] . '</p>' : ((Config::get('showHelp') && isset($GLOBALS['TL_LANG']['MSC']['all_fields'][1])) ? '
<p class="tl_help tl_tip">' . $GLOBALS['TL_LANG']['MSC']['all_fields'][1] . '</p>' : '')) . '
</div>
</div>
</div>
<div class="tl_formbody_submit">
<div class="tl_submit_container">
  <button type="submit" name="save" id="save" class="tl_submit" accesskey="s">' . $GLOBALS['TL_LANG']['MSC']['continue'] . '</button>
</div>
</div>
</form>';
		}

		// Return
		return Message::generate() . ($this->noReload ? '
<p class="tl_error">' . $GLOBALS['TL_LANG']['ERR']['submit'] . '</p>' : '') . '
<div id="tl_buttons">
<a href="' . $this->getReferer(true) . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b" data-action="contao--scroll-offset#discard">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>' . $return;
	}

	/**
	 * List all records of the current table and return them as HTML string
	 *
	 * @return string
	 */
	protected function listView()
	{
		$table = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) == self::MODE_TREE_EXTENDED ? $this->ptable : $this->strTable;
		$orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'] ?? array('id');
		$firstOrderBy = preg_replace('/\s+.*$/', '', $orderBy[0]);

		if (\is_array($this->orderBy) && !empty($this->orderBy[0]))
		{
			$orderBy = $this->orderBy;
			$firstOrderBy = $this->firstOrderBy;
		}

		// Check the default labels (see #509)
		$labelNew = $GLOBALS['TL_LANG'][$this->strTable]['new'] ?? $GLOBALS['TL_LANG']['DCA']['new'];
		$limitHeight = BackendUser::getInstance()->doNotCollapse ? false : (int) ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['limitHeight'] ?? 0);

		$query = "SELECT * FROM " . $this->strTable;

		// Show only main products
		$this->procedure[] = "pid=0";

		if (!empty($this->procedure))
		{
			$query .= " WHERE " . implode(' AND ', $this->procedure);
		}

		if (!empty($this->root) && \is_array($this->root))
		{
			$query .= (!empty($this->procedure) ? " AND " : " WHERE ") . "id IN(" . implode(',', array_map('\intval', $this->root)) . ")";
		}

		$db = Database::getInstance();

		if (\is_array($orderBy) && $orderBy[0])
		{
			foreach ($orderBy as $k=>$v)
			{
				list($key, $direction) = explode(' ', $v, 2) + array(null, null);

				$orderBy[$k] = $key;

				// If there is no direction, check the global flag in sorting mode 1 or the field flag in all other sorting modes
				if (!$direction)
				{
					if (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) == self::MODE_SORTED && \in_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] ?? null, array(self::SORT_INITIAL_LETTER_DESC, self::SORT_INITIAL_LETTERS_DESC, self::SORT_DAY_DESC, self::SORT_MONTH_DESC, self::SORT_YEAR_DESC, self::SORT_DESC)))
					{
						$direction = 'DESC';
					}
					elseif (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['flag'] ?? null, array(self::SORT_INITIAL_LETTER_DESC, self::SORT_INITIAL_LETTERS_DESC, self::SORT_DAY_DESC, self::SORT_MONTH_DESC, self::SORT_YEAR_DESC, self::SORT_DESC)))
					{
						$direction = 'DESC';
					}
				}

				if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['foreignKey']))
				{
					$chunks = explode('.', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['foreignKey'], 2);
					$orderBy[$k] = "(SELECT " . Database::quoteIdentifier($chunks[1]) . " FROM " . $chunks[0] . " WHERE " . $chunks[0] . ".id=" . $this->strTable . "." . $key . ")";
				}

				if (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['flag'] ?? null, array(self::SORT_DAY_ASC, self::SORT_DAY_DESC, self::SORT_DAY_BOTH, self::SORT_MONTH_ASC, self::SORT_MONTH_DESC, self::SORT_MONTH_BOTH, self::SORT_YEAR_ASC, self::SORT_YEAR_DESC, self::SORT_YEAR_BOTH)))
				{
					$orderBy[$k] = "CAST(" . $orderBy[$k] . " AS SIGNED)"; // see #5503
				}

				if ($direction)
				{
					$orderBy[$k] .= ' ' . $direction;
				}

				if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['eval']['findInSet'] ?? null)
				{
					if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback'] ?? null))
					{
						$strClass = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback'][0];
						$strMethod = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback'][1];

						$keys = System::importStatic($strClass)->$strMethod($this);
					}
					elseif (\is_callable($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback'] ?? null))
					{
						$keys = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback']($this);
					}
					else
					{
						$keys = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options'] ?? array();
					}

					if (($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['eval']['isAssociative'] ?? null) || ArrayUtil::isAssoc($keys))
					{
						$keys = array_keys($keys);
					}

					$orderBy[$k] = $db->findInSet($v, $keys);
				}
			}

			if (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) == self::MODE_SORTED_PARENT)
			{
				$firstOrderBy = 'pid';
				$showFields = $GLOBALS['TL_DCA'][$table]['list']['label']['fields'];

				$query .= " ORDER BY (SELECT " . Database::quoteIdentifier($showFields[0]) . " FROM " . $this->ptable . " WHERE " . $this->ptable . ".id=" . $this->strTable . ".pid), " . implode(', ', $orderBy) . ', id';

				// Set the foreignKey so that the label is translated
				if (!($GLOBALS['TL_DCA'][$table]['fields']['pid']['foreignKey'] ?? null))
				{
					$GLOBALS['TL_DCA'][$table]['fields']['pid']['foreignKey'] = $this->ptable . '.' . $showFields[0];
				}

				// Remove the parent field from label fields
				array_shift($showFields);
				$GLOBALS['TL_DCA'][$table]['list']['label']['fields'] = $showFields;
			}
			else
			{
				$query .= " ORDER BY " . implode(', ', $orderBy) . ', id';
			}
		}

		$objRowStmt = $db->prepare($query);

		if ($this->limit)
		{
			$arrLimit = explode(',', $this->limit) + array(null, null);
			$objRowStmt->limit($arrLimit[1], $arrLimit[0]);
		}

		$objRow = $objRowStmt->execute(...$this->values);
		$security = System::getContainer()->get('security.helper');

		// Display buttons
		$buttons = ((Input::get('act') == 'select') ? '
<a href="' . $this->getReferer(true, $this->ptable) . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b" data-action="contao--scroll-offset#discard">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a> ' : (isset($GLOBALS['TL_DCA'][$this->strTable]['config']['backlink']) ? '
<a href="' . System::getContainer()->get('router')->generate('contao_backend') . '?' . $GLOBALS['TL_DCA'][$this->strTable]['config']['backlink'] . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b" data-action="contao--scroll-offset#discard">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a> ' : '')) . ((Input::get('act') != 'select' && !($GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable'] ?? null) && $security->isGranted(ContaoCorePermissions::DC_PREFIX . $this->strTable, new CreateAction($this->strTable))) ? '
<a href="' . ($this->ptable ? $this->addToUrl('act=create' . ((($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) < self::MODE_PARENT) ? '&amp;mode=2' : '') . '&amp;pid=' . $this->intId) : $this->addToUrl('act=create')) . '" class="header_new" title="' . StringUtil::specialchars($labelNew[1] ?? '') . '" accesskey="n" data-action="contao--scroll-offset#store">' . $labelNew[0] . '</a> ' : '') . $this->generateGlobalButtons();

		$return = Message::generate() . ($buttons ? '<div id="tl_buttons">' . $buttons . '</div>' : '');

		// Return "no records found" message
		if ($objRow->numRows < 1)
		{
			$return .= '
<p class="tl_empty">' . $GLOBALS['TL_LANG']['MSC']['noResult'] . '</p>';
		}

		// List records
		else
		{
			$result = $objRow->fetchAllAssoc();

			$return .= ((Input::get('act') == 'select') ? '
<form id="tl_select" class="tl_form' . ((Input::get('act') == 'select') ? ' unselectable' : '') . '" method="post" novalidate>
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_select">
<input type="hidden" name="REQUEST_TOKEN" value="' . htmlspecialchars(System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue(), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5) . '">' : '') . '
<div class="tl_listing_container iso_listing_container list_view" id="tl_listing"' . $this->getPickerValueAttribute() . '>' . (isset($GLOBALS['TL_DCA'][$table]['list']['sorting']['breadcrumb']) ? $GLOBALS['TL_DCA'][$table]['list']['sorting']['breadcrumb'] : '') . ((Input::get('act') == 'select' || $this->strPickerFieldType == 'checkbox') ? '
<div class="tl_select_trigger">
<label for="tl_select_trigger" class="tl_select_label">' . $GLOBALS['TL_LANG']['MSC']['selectAll'] . '</label> <input type="checkbox" id="tl_select_trigger" onclick="Backend.toggleCheckboxes(this)" class="tl_tree_checkbox">
</div>' : '') . '
<table class="tl_listing' . (($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ?? null) ? ' showColumns' : '') . ($this->strPickerFieldType ? ' picker unselectable' : '') . '">';

			// Automatically add the "order by" field as last column if we do not have group headers
			if (($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ?? null) && false !== ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showFirstOrderBy'] ?? null))
			{
				$blnFound = false;

				// Extract the real key and compare it to $firstOrderBy
				foreach ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'] as $f)
				{
					if (str_contains($f, ':'))
					{
						list($f) = explode(':', $f, 2);
					}

					if ($firstOrderBy == $f)
					{
						$blnFound = true;
						break;
					}
				}

				if (!$blnFound)
				{
					$GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'][] = $firstOrderBy;
				}
			}

			// Generate the table header if the "show columns" option is active
			if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ?? null)
			{
				$return .= '
<thead>
  <tr>';

				foreach ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'] as $f)
				{
					if (str_contains($f, ':'))
					{
						list($f) = explode(':', $f, 2);
					}

					$return .= '
    <th class="tl_folder_tlist col_' . $f . (($f == $firstOrderBy) ? ' ordered_by' : '') . '">' . (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label'] ?? null) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label'][0] : ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label'] ?? $f)) . '</th>';
				}

				$return .= '
    <th class="tl_folder_tlist tl_right_nowrap"></th>
  </tr>
</thead>
<tbody>';
			}

			// Process result and add label and buttons
			$remoteCur = false;
			$groupclass = 'tl_folder_tlist';

			foreach ($result as $row)
			{
				// Improve performance for $dc->getCurrentRecord($id);
				static::setCurrentRecordCache($row['id'], $this->strTable, $row);

				$this->denyAccessUnlessGranted(ContaoCorePermissions::DC_PREFIX . $this->strTable, new ReadAction($this->strTable, $row));

				$this->current[] = $row['id'];
				$label = $this->generateRecordLabel($row, $this->strTable);

				// Build the sorting groups
				if (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) > 0)
				{
					$current = $row[$firstOrderBy];
					$orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'] ?? array('id');
					$sortingMode = (\count($orderBy) == 1 && $firstOrderBy == $orderBy[0] && ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'] ?? null)) ? $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] : ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'] ?? null);
					$remoteNew = $this->formatCurrentValue($firstOrderBy, $current, $sortingMode);

					// Add the group header
					if (($remoteNew != $remoteCur || $remoteCur === false) && !($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['disableGrouping'] ?? null))
					{
						$group = $this->formatGroupHeader($firstOrderBy, $remoteNew, $sortingMode, $row);
						$remoteCur = $remoteNew;

						$return .= '
  <tr>
    <th colspan="2" class="' . $groupclass . '">' . $group . '</th>
  </tr>';
						$groupclass = 'tl_folder_list';
					}
				}

				$return .= '
  <tr class="' . ((string) ($row['tstamp'] ?? null) === '0' ? 'draft ' : '') . 'click2edit toggle_select hover-row" data-controller="contao--deeplink contao--operations-menu" data-action="contextmenu->contao--operations-menu#open">
    ';

				$colspan = 1;

				// Handle strings and arrays
				if (!($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ?? null))
				{
					$label = \is_array($label) ? implode(' ', $label) : $label;
				}
				elseif (!\is_array($label))
				{
					$label = array($label);
					$colspan = \count($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'] ?? array());
				}

				// Show columns
				if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ?? null)
				{
					foreach ($label as $j=>$arg)
					{
						$field = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'][$j] ?? null;
						$value = (string) $arg !== '' ? $arg : '-';

						$return .= '<td colspan="' . $colspan . '" class="tl_file_list col_' . explode(':', $field, 2)[0] . ($field == $firstOrderBy ? ' ordered_by' : '') . '">' . $value . '</td>';
					}
				}
				else
				{
					$return .= '<td class="tl_file_list">' . ($limitHeight ? '<div data-contao--limit-height-target="node">' : '') . $label . ($limitHeight ? '</div>' : '') . '</td>';
				}

				// Buttons ($row, $table, $root, $blnCircularReference, $children, $previous, $next)
				$return .= ((Input::get('act') == 'select') ? '
	<td class="tl_file_list tl_right_nowrap iso_operations"><input type="checkbox" name="IDS[]" id="ids_' . $row['id'] . '" class="tl_tree_checkbox" value="' . $row['id'] . '"></td>' : '
	<td class="tl_file_list tl_right_nowrap iso_operations">' . $this->generateButtons($row, $this->strTable, $this->root) . ($this->strPickerFieldType ? $this->getPickerInputField($row['id']) : '') . '</td>') . '
  </tr>';
			}

			// Close the table body if the "show columns" option is active
			if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ?? null)
			{
				$return .= '</tbody>';
			}

			// Close the table
			$return .= '
</table>' . ($this->strPickerFieldType == 'radio' ? '
<div class="tl_radio_reset">
<label for="tl_radio_reset" class="tl_radio_label">' . $GLOBALS['TL_LANG']['MSC']['resetSelected'] . '</label> <input type="radio" name="picker" id="tl_radio_reset" value="" class="tl_tree_radio">
</div>' : '') . '
</div>';

			// Add another panel at the end of the page
			if (str_contains($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['panelLayout'] ?? '', 'limit'))
			{
				$return .= $this->paginationMenu();
			}

			// Close the form
			if (Input::get('act') == 'select')
			{
				// Submit buttons
				$arrButtons = array();

				if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'] ?? null))
				{
					$arrButtons['edit'] = '<button type="submit" name="edit" id="edit" class="tl_submit" accesskey="s">' . $GLOBALS['TL_LANG']['MSC']['editSelected'] . '</button>';
				}

				if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['notDeletable'] ?? null))
				{
					$arrButtons['delete'] = '<button type="submit" name="delete" id="delete" class="tl_submit" accesskey="d" onclick="return confirm(\'' . $GLOBALS['TL_LANG']['MSC']['delAllConfirm'] . '\')">' . $GLOBALS['TL_LANG']['MSC']['deleteSelected'] . '</button>';
				}

				if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['notSortable'] ?? null))
				{
					$arrButtons['cut'] = '<button type="submit" name="cut" id="cut" class="tl_submit" accesskey="x">' . $GLOBALS['TL_LANG']['MSC']['moveSelected'] . '</button>';
				}

				if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['notCopyable'] ?? null))
				{
					$arrButtons['copy'] = '<button type="submit" name="copy" id="copy" class="tl_submit" accesskey="c">' . $GLOBALS['TL_LANG']['MSC']['copySelected'] . '</button>';
				}

				if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'] ?? null))
				{
					$arrButtons['override'] = '<button type="submit" name="override" id="override" class="tl_submit" accesskey="v">' . $GLOBALS['TL_LANG']['MSC']['overrideSelected'] . '</button>';
				}

				// Call the buttons_callback (see #4691)
				if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['select']['buttons_callback'] ?? null))
				{
					foreach ($GLOBALS['TL_DCA'][$this->strTable]['select']['buttons_callback'] as $callback)
					{
						if (\is_array($callback))
						{
							$arrButtons = System::importStatic($callback[0])->{$callback[1]}($arrButtons, $this);
						}
						elseif (\is_callable($callback))
						{
							$arrButtons = $callback($arrButtons, $this);
						}
					}
				}

				if (\count($arrButtons) < 3)
				{
					$strButtons = implode(' ', $arrButtons);
				}
				else
				{
					$strButtons = array_shift($arrButtons) . ' ';
					$strButtons .= '<div class="split-button">';
					$strButtons .= array_shift($arrButtons) . '<button type="button" id="sbtog">' . Image::getHtml('navcol.svg') . '</button> <ul class="invisible">';

					foreach ($arrButtons as $strButton)
					{
						$strButtons .= '<li>' . $strButton . '</li>';
					}

					$strButtons .= '</ul></div>';
				}

				$return .= '
</div>
<div class="tl_formbody_submit" style="text-align:right">
<div class="tl_submit_container">
  ' . $strButtons . '
</div>
</div>
</form>';
			}
		}

		if ($limitHeight)
		{
			$return = '<div
				data-controller="contao--limit-height"
				data-contao--limit-height-max-value="' . $limitHeight . '"
				data-contao--limit-height-expand-value="' . $GLOBALS['TL_LANG']['MSC']['expandNode'] . '"
				data-contao--limit-height-collapse-value="' . $GLOBALS['TL_LANG']['MSC']['collapseNode'] . '"
				data-contao--limit-height-expand-all-value="' . $GLOBALS['TL_LANG']['DCA']['expandNodes'][0] . '"
				data-contao--limit-height-expand-all-title-value="' . $GLOBALS['TL_LANG']['DCA']['expandNodes'][1] . '"
				data-contao--limit-height-collapse-all-value="' . $GLOBALS['TL_LANG']['DCA']['collapseNodes'][0] . '"
				data-contao--limit-height-collapse-all-title-value="' . $GLOBALS['TL_LANG']['DCA']['collapseNodes'][0] . '"
			>' . $return . '</div>';
		}

		return $return;
	}

	/**
	 * Show header of the parent table and list all records of the current table
	 *
	 * @return string
	 */
	protected function parentView()
	{
		$objSession = System::getContainer()->get('request_stack')->getSession();

		$blnClipboard = false;
		$arrClipboard = $objSession->get('CLIPBOARD');
		$table = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) == self::MODE_TREE_EXTENDED ? $this->ptable : $this->strTable;
		$blnHasSorting = false;
		$blnMultiboard = false;

		// Check clipboard
		if (!empty($arrClipboard[$table]))
		{
			$blnClipboard = true;
			$arrClipboard = $arrClipboard[$table];

			if (\is_array($arrClipboard['id'] ?? null))
			{
				$blnMultiboard = true;
			}
		}
		else
		{
			$arrClipboard = null;
		}

		// Check the default labels (see #509)
		$labelNew = $GLOBALS['TL_LANG'][$this->strTable]['new'] ?? $GLOBALS['TL_LANG']['DCA']['new'];
		$labelCut = $GLOBALS['TL_LANG'][$this->strTable]['cut'] ?? $GLOBALS['TL_LANG']['DCA']['cut'];
		$labelPasteNew = $GLOBALS['TL_LANG'][$this->strTable]['pastenew'] ?? $GLOBALS['TL_LANG']['DCA']['pastenew'] ?? $GLOBALS['TL_LANG']['DCA']['pastetop'];
		$labelPasteAfter = $GLOBALS['TL_LANG'][$this->strTable]['pasteafter'] ?? $GLOBALS['TL_LANG']['DCA']['pasteafter'];
		$labelEditHeader = $GLOBALS['TL_LANG'][$this->ptable]['edit'] ?? $GLOBALS['TL_LANG']['DCA']['edit'];
		$limitHeight = BackendUser::getInstance()->doNotCollapse ? false : (int) ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['limitHeight'] ?? 0);
		$strBackUrl = Input::get('id') ? System::getContainer()->get('router')->generate('contao_backend').'?do=iso_products' : System::getReferer(true, $this->ptable);

		$db = Database::getInstance();
		$security = System::getContainer()->get('security.helper');

		// TODO: fix back button in variants
		$buttons = (Input::get('nb') ? '' : '
<a href="' . $strBackUrl . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b" data-action="contao--scroll-offset#discard">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>') . ' ' . ((Input::get('act') != 'select' && !$blnClipboard && !($GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable'] ?? null) && $security->isGranted(ContaoCorePermissions::DC_PREFIX . $this->strTable, new CreateAction($this->strTable, array('pid' => $this->intCurrentPid)))) ? '
<a href="' . $this->addToUrl($blnHasSorting ? 'act=paste&amp;mode=create' : 'act=create&amp;mode=2&amp;pid=' . $this->intId) . '" class="header_new" title="' . StringUtil::specialchars($labelNew[1]) . '" accesskey="n" data-action="contao--scroll-offset#store">' . $labelNew[0] . '</a> ' : '') . ($blnClipboard ? '
<a href="' . $this->addToUrl('clipboard=1') . '" class="header_clipboard" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['clearClipboard']) . '" accesskey="x">' . $GLOBALS['TL_LANG']['MSC']['clearClipboard'] . '</a> ' : $this->generateGlobalButtons());

		$return = Message::generate() . ($buttons ? '<div id="tl_buttons">' . $buttons . '</div>' : '');

		// Get all details of the parent record
		$objParent = $db
			->prepare("SELECT * FROM " . $this->ptable . " WHERE id=?")
			->limit(1)
			->execute($this->intCurrentPid);

		if ($objParent->numRows < 1)
		{
			return $return;
		}

		$return .= ((Input::get('act') == 'select') ? '

<form id="tl_select" class="tl_form' . ((Input::get('act') == 'select') ? ' unselectable' : '') . '" method="post" novalidate>
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_select">
<input type="hidden" name="REQUEST_TOKEN" value="' . htmlspecialchars(System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue(), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5) . '">' : '') . ($blnClipboard ? '
<div id="paste_hint">
  <p>' . $GLOBALS['TL_LANG']['MSC']['selectNewPosition'] . '</p>
</div>' : '') . '
<div class="tl_listing_container iso_listing_container parent_view' . (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['renderAsGrid'] ?? false) ? ' as-grid' : '') . ($this->strPickerFieldType ? ' picker unselectable' : '') . '" id="tl_listing"' . $this->getPickerValueAttribute() . '>
<div class="tl_header click2edit toggle_select hover-div" data-controller="contao--deeplink contao--operations-menu" data-action="contextmenu->contao--operations-menu#open">';

		// List all records of the child table
		if (\in_array(Input::get('act'), array('select', null)))
		{
			// Header
			$imagePasteNew = Image::getHtml('new.svg', $labelPasteNew[0]);
			$imagePasteAfter = Image::getHtml('pasteafter.svg', $labelPasteAfter[0]);
			$imageEditHeader = Image::getHtml('edit.svg', \sprintf(\is_array($labelEditHeader) ? $labelEditHeader[0] : $labelEditHeader, $objParent->id));

			$security = System::getContainer()->get('security.helper');

			$return .= '
<div class="tl_content_right">' . ((Input::get('act') == 'select' || $this->strPickerFieldType == 'checkbox') ? '
<label for="tl_select_trigger" class="tl_select_label">' . $GLOBALS['TL_LANG']['MSC']['selectAll'] . '</label> <input type="checkbox" id="tl_select_trigger" onclick="Backend.toggleCheckboxes(this)" class="tl_tree_checkbox">' : ($blnClipboard ? '
<a href="' . $this->addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=2&amp;pid=' . $objParent->id . (!$blnMultiboard ? '&amp;id=' . $arrClipboard['id'] : '')) . '" title="' . StringUtil::specialchars($labelPasteAfter[0]) . '" data-action="contao--scroll-offset#store">' . $imagePasteAfter . '</a>' : ((!($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'] ?? null) && $security->isGranted(ContaoCorePermissions::USER_CAN_EDIT_FIELDS_OF_TABLE, $this->strTable) && $security->isGranted(ContaoCorePermissions::DC_PREFIX . $this->strTable, new UpdateAction($this->strTable, $objParent->row()))) ? '
<a href="' . preg_replace('/&(amp;)?table=[^& ]*/i', $this->ptable ? '&amp;table=' . $this->ptable : '', $this->addToUrl('act=edit' . (Input::get('nb') ? '&amp;nc=1' : ''))) . '" class="edit" title="' . StringUtil::specialchars(\sprintf(\is_array($labelEditHeader) ? $labelEditHeader[1] : $labelEditHeader, $objParent->id)) . '">' . $imageEditHeader . '</a> ' . $this->generateHeaderButtons($objParent->row(), $this->strTable) : '') . (($blnHasSorting && !($GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable'] ?? null) && $security->isGranted(ContaoCorePermissions::DC_PREFIX . $this->strTable, new CreateAction($this->strTable, array('pid' => $objParent->id, 'sorting' => 0)))) ? '
<a href="' . $this->addToUrl('act=create&amp;mode=2&amp;pid=' . $objParent->id . '&amp;id=' . $this->intId) . '" title="' . StringUtil::specialchars($labelPasteNew[0]) . '">' . $imagePasteNew . '</a>' : ''))) . '
</div>';

			// Format header fields
			$add = array();
			$headerFields = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['headerFields'];

			foreach ($headerFields as $v)
			{
				$_v = StringUtil::deserialize($objParent->$v);

				// Translate UUIDs to paths
				if (($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['inputType'] ?? null) == 'fileTree')
				{
					$objFiles = FilesModel::findMultipleByUuids((array) $_v);

					if ($objFiles !== null)
					{
						$_v = $objFiles->fetchEach('path');
					}
				}

				if (\is_array($_v))
				{
					$_v = implode(', ', $_v);
				}
				elseif (($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['eval']['isBoolean'] ?? null) || (($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['inputType'] ?? null) == 'checkbox' && !($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['eval']['multiple'] ?? null)))
				{
					$_v = $_v ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
				}
				elseif (($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['eval']['rgxp'] ?? null) == 'date')
				{
					$_v = $_v ? Date::parse(Config::get('dateFormat'), $_v) : '-';
				}
				elseif (($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['eval']['rgxp'] ?? null) == 'time')
				{
					$_v = $_v ? Date::parse(Config::get('timeFormat'), $_v) : '-';
				}
				elseif (($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['eval']['rgxp'] ?? null) == 'datim')
				{
					$_v = $_v ? Date::parse(Config::get('datimFormat'), $_v) : '-';
				}
				elseif ($v == 'tstamp')
				{
					$_v = Date::parse(Config::get('datimFormat'), $objParent->tstamp);
				}
				elseif (isset($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['foreignKey']))
				{
					$arrForeignKey = explode('.', $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['foreignKey'], 2);

					$objLabel = $db
						->prepare("SELECT " . Database::quoteIdentifier($arrForeignKey[1]) . " AS value FROM " . $arrForeignKey[0] . " WHERE id=?")
						->limit(1)
						->execute($_v);

					$_v = $objLabel->numRows ? $objLabel->value : '-';
				}
				elseif (\is_array($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['reference'][$_v] ?? null))
				{
					$_v = $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['reference'][$_v][0];
				}
				elseif (isset($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['reference'][$_v]))
				{
					$_v = $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['reference'][$_v];
				}
				elseif (($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['eval']['isAssociative'] ?? null) || ArrayUtil::isAssoc($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['options'] ?? null))
				{
					$_v = $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['options'][$_v] ?? null;
				}
				elseif (\is_array($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['options_callback'] ?? null))
				{
					$strClass = $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['options_callback'][0];
					$strMethod = $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['options_callback'][1];

					$options_callback = System::importStatic($strClass)->$strMethod($this);

					$_v = $options_callback[$_v] ?? '-';
				}
				elseif (\is_callable($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['options_callback'] ?? null))
				{
					$options_callback = $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['options_callback']($this);

					$_v = $options_callback[$_v] ?? '-';
				}

				// Add the sorting field
				if ($_v)
				{
					if (isset($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['label']))
					{
						$key = \is_array($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['label']) ? $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['label'][0] : $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['label'];
					}
					else
					{
						$key = $GLOBALS['TL_LANG'][$this->ptable][$v][0] ?? $v;
					}

					$add[$key] = $_v;
				}
			}

			// Trigger the header_callback (see #3417)
			if (\is_array($GLOBALS['TL_DCA'][$table]['list']['sorting']['header_callback'] ?? null))
			{
				$add = System::importStatic($GLOBALS['TL_DCA'][$table]['list']['sorting']['header_callback'][0])->{$GLOBALS['TL_DCA'][$table]['list']['sorting']['header_callback'][1]}($add, $this);
			}
			elseif (\is_callable($GLOBALS['TL_DCA'][$table]['list']['sorting']['header_callback'] ?? null))
			{
				$add = $GLOBALS['TL_DCA'][$table]['list']['sorting']['header_callback']($add, $this);
			}

			// Output the header data
			$return .= '

<table class="tl_header_table">';

			foreach ($add as $k=>$v)
			{
				if (\is_array($v))
				{
					$v = $v[0];
				}

				$return .= '
  <tr>
    <td><span class="tl_label">' . $k . ':</span> </td>
    <td>' . $v . '</td>
  </tr>';
			}

			$return .= '
</table>
</div>';

			$orderBy = array();
			$firstOrderBy = array();

			// Add all records of the current table
			$query = "SELECT * FROM " . $this->strTable;

			if (\is_array($this->orderBy) && isset($this->orderBy[0]))
			{
				$orderBy = $this->orderBy;
				$firstOrderBy = preg_replace('/\s+.*$/', '', $orderBy[0]);

				// Order by the foreign key
				if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['foreignKey']))
				{
					$key = explode('.', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['foreignKey'], 2);
					$orderBy[0] = "(SELECT " . Database::quoteIdentifier($key[1]) . " FROM " . $key[0] . " WHERE " . $this->strTable . "." . Database::quoteIdentifier($firstOrderBy) . "=" . $key[0] . ".id)";
				}
			}
			elseif (\is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'] ?? null))
			{
				$orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
				$firstOrderBy = preg_replace('/\s+.*$/', '', $orderBy[0]);
			}

			$arrProcedure = $this->procedure;
			$arrValues = $this->values;

			$arrProcedure[] = "pid=?";
			$arrValues[] = $this->intCurrentPid;

			// WHERE
			if (!empty($arrProcedure))
			{
				$query .= " WHERE " . implode(' AND ', $arrProcedure);
			}

			if (!empty($this->root) && \is_array($this->root))
			{
				$query .= (!empty($arrProcedure) ? " AND " : " WHERE ") . "id IN(" . implode(',', array_map('\intval', $this->root)) . ")";
			}

			// ORDER BY
			if (!empty($orderBy) && \is_array($orderBy))
			{
				foreach ($orderBy as $k=>$v)
				{
					if (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['flag'] ?? null, array(self::SORT_INITIAL_LETTER_DESC, self::SORT_INITIAL_LETTERS_DESC, self::SORT_DAY_DESC, self::SORT_MONTH_DESC, self::SORT_YEAR_DESC, self::SORT_DESC)))
					{
						$orderBy[$k] .= ' DESC';
					}
				}

				$query .= " ORDER BY " . implode(', ', $orderBy) . ', id';
			}

			$objOrderByStmt = $db->prepare($query);

			// LIMIT
			if ($this->limit)
			{
				$arrLimit = explode(',', $this->limit) + array(null, null);
				$objOrderByStmt->limit($arrLimit[1], $arrLimit[0]);
			}

			$objOrderBy = $objOrderByStmt->execute(...$arrValues);

			if ($objOrderBy->numRows < 1)
			{
				return $return . '
<p class="tl_empty_parent_view">' . $GLOBALS['TL_LANG']['MSC']['noResult'] . '</p>
</div>';
			}

			$result = $objOrderBy->fetchAllAssoc();
			$return .= '

<table class="tl_listing' . (($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ?? null) ? ' showColumns' : '') . ($this->strPickerFieldType ? ' picker unselectable' : '') . '">';

			// Automatically add the "order by" field as last column if we do not have group headers
			if (($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ?? null) && false !== ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showFirstOrderBy'] ?? null))
			{
				$blnFound = false;

				// Extract the real key and compare it to $firstOrderBy
				foreach ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'] as $f)
				{
					if (str_contains($f, ':'))
					{
						list($f) = explode(':', $f, 2);
					}

					if ($firstOrderBy == $f)
					{
						$blnFound = true;
						break;
					}
				}

				if (!$blnFound)
				{
					$GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'][] = $firstOrderBy;
				}
			}

			// Generate the table header if the "show columns" option is active
			if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ?? null)
			{
				$return .= '
<thead>
  <tr>';

				foreach ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'] as $f)
				{
					if (str_contains($f, ':'))
					{
						list($f) = explode(':', $f, 2);
					}

					$return .= '
	<th class="tl_folder_tlist col_' . $f . (($f == $firstOrderBy) ? ' ordered_by' : '') . '">' . (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label'] ?? null) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label'][0] : ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label'] ?? $f)) . '</th>';
				}

				$return .= '
	<th class="tl_folder_tlist tl_right_nowrap iso_operations"></th>
  </tr>
</thead>
<tbody>';
			}

			// Process result and add label and buttons
			$remoteCur = false;
			$groupclass = 'tl_folder_tlist';

			foreach ($result as $row)
			{
				// Improve performance for $dc->getCurrentRecord($id);
				static::setCurrentRecordCache($row['id'], $this->strTable, $row);

				$this->denyAccessUnlessGranted(ContaoCorePermissions::DC_PREFIX . $this->strTable, new ReadAction($this->strTable, $row));

				$this->current[] = $row['id'];
				$label = $this->generateRecordLabel($row, $this->strTable);

				// Build the sorting groups
				if (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) > 0)
				{
					$current = $row[$firstOrderBy];
					$orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'] ?? array('id');
					$sortingMode = (\count($orderBy) == 1 && $firstOrderBy == $orderBy[0] && ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'] ?? null)) ? $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] : ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'] ?? null);
					$remoteNew = $this->formatCurrentValue($firstOrderBy, $current, $sortingMode);

					// Add the group header
					if (($remoteNew != $remoteCur || $remoteCur === false) && !($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['disableGrouping'] ?? null))
					{
						$group = $this->formatGroupHeader($firstOrderBy, $remoteNew, $sortingMode, $row);
						$remoteCur = $remoteNew;

						$return .= '
  <tr>
	<th colspan="2" class="' . $groupclass . '">' . $group . '</th>
  </tr>';
						$groupclass = 'tl_folder_list';
					}
				}

				$return .= '
  <tr class="' . ((string) ($row['tstamp'] ?? null) === '0' ? 'draft ' : '') . 'click2edit toggle_select hover-row" data-controller="contao--deeplink contao--operations-menu" data-action="contextmenu->contao--operations-menu#open">
	';

				$colspan = 1;

				// Handle strings and arrays
				if (!($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ?? null))
				{
					$label = \is_array($label) ? implode(' ', $label) : $label;
				}
				elseif (!\is_array($label))
				{
					$label = array($label);
					$colspan = \count($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'] ?? array());
				}

				// Show columns
				if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ?? null)
				{
					foreach ($label as $j=>$arg)
					{
						$field = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'][$j] ?? null;

						if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['foreignKey']))
						{
							if ($arg)
							{
								$key = explode('.', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['foreignKey'], 2);

								$reference = $this->Database
									->prepare("SELECT " . Database::quoteIdentifier($key[1]) . " AS value FROM " . $key[0] . " WHERE id=?")
									->limit(1)
									->execute($arg);

								if ($reference->numRows)
								{
									$arg = $reference->value;
								}
							}

							$value = $arg ?: '-';
						}
						else
						{
							$value = (string) $arg !== '' ? $arg : '-';
						}

						$return .= '<td colspan="' . $colspan . '" class="tl_file_list col_' . explode(':', $field, 2)[0] . ($field == $firstOrderBy ? ' ordered_by' : '') . '">' . $value . '</td>';
					}
				}
				else
				{
					$return .= '<td class="tl_file_list">' . ($limitHeight ? '<div data-contao--limit-height-target="node">' : '') . $label . ($limitHeight ? '</div>' : '') . '</td>';
				}

				// Buttons ($row, $table, $root, $blnCircularReference, $children, $previous, $next)
				$return .= ((Input::get('act') == 'select') ? '
	<td class="tl_file_list tl_right_nowrap iso_operations"><input type="checkbox" name="IDS[]" id="ids_' . $row['id'] . '" class="tl_tree_checkbox" value="' . $row['id'] . '"></td>' : '
	<td class="tl_file_list tl_right_nowrap iso_operations">' . $this->generateButtons($row, $this->strTable, $this->root) . ($this->strPickerFieldType ? $this->getPickerInputField($row['id']) : '') . '</td>') . '
  </tr>';
			}

			// Close the table body if the "show columns" option is active
			if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ?? null)
			{
				$return .= '</tbody>';
			}

			// Close the table
			$return .= '
</table>' . ($this->strPickerFieldType == 'radio' ? '
<div class="tl_radio_reset">
<label for="tl_radio_reset" class="tl_radio_label">' . $GLOBALS['TL_LANG']['MSC']['resetSelected'] . '</label> <input type="radio" name="picker" id="tl_radio_reset" value="" class="tl_tree_radio">
</div>' : '') . '
</div>';

			// Add another panel at the end of the page
			if (str_contains($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['panelLayout'] ?? '', 'limit'))
			{
				$return .= $this->paginationMenu();
			}

			// Close the form
			if (Input::get('act') == 'select')
			{
				// Submit buttons
				$arrButtons = array();

				if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'] ?? null))
				{
					$arrButtons['edit'] = '<button type="submit" name="edit" id="edit" class="tl_submit" accesskey="s">' . $GLOBALS['TL_LANG']['MSC']['editSelected'] . '</button>';
				}

				if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['notDeletable'] ?? null))
				{
					$arrButtons['delete'] = '<button type="submit" name="delete" id="delete" class="tl_submit" accesskey="d" onclick="return confirm(\'' . $GLOBALS['TL_LANG']['MSC']['delAllConfirm'] . '\')">' . $GLOBALS['TL_LANG']['MSC']['deleteSelected'] . '</button>';
				}

				if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['notSortable'] ?? null))
				{
					$arrButtons['cut'] = '<button type="submit" name="cut" id="cut" class="tl_submit" accesskey="x">' . $GLOBALS['TL_LANG']['MSC']['moveSelected'] . '</button>';
				}

				if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['notCopyable'] ?? null))
				{
					$arrButtons['copy'] = '<button type="submit" name="copy" id="copy" class="tl_submit" accesskey="c">' . $GLOBALS['TL_LANG']['MSC']['copySelected'] . '</button>';
				}

				if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'] ?? null))
				{
					$arrButtons['override'] = '<button type="submit" name="override" id="override" class="tl_submit" accesskey="v">' . $GLOBALS['TL_LANG']['MSC']['overrideSelected'] . '</button>';
				}

				// Call the buttons_callback (see #4691)
				if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['select']['buttons_callback'] ?? null))
				{
					foreach ($GLOBALS['TL_DCA'][$this->strTable]['select']['buttons_callback'] as $callback)
					{
						if (\is_array($callback))
						{
							$arrButtons = System::importStatic($callback[0])->{$callback[1]}($arrButtons, $this);
						}
						elseif (\is_callable($callback))
						{
							$arrButtons = $callback($arrButtons, $this);
						}
					}
				}

				if (\count($arrButtons) < 3)
				{
					$strButtons = implode(' ', $arrButtons);
				}
				else
				{
					$strButtons = array_shift($arrButtons) . ' ';
					$strButtons .= '<div class="split-button">';
					$strButtons .= array_shift($arrButtons) . '<button type="button" id="sbtog">' . Image::getHtml('navcol.svg') . '</button> <ul class="invisible">';

					foreach ($arrButtons as $strButton)
					{
						$strButtons .= '<li>' . $strButton . '</li>';
					}

					$strButtons .= '</ul></div>';
				}

				$return .= '
</div>
<div class="tl_formbody_submit" style="text-align:right">
<div class="tl_submit_container">
  ' . $strButtons . '
</div>
</div>
</form>';
			}
		}

		if ($limitHeight)
		{
			$return = '<div
				data-controller="contao--limit-height"
				data-contao--limit-height-max-value="' . $limitHeight . '"
				data-contao--limit-height-expand-value="' . $GLOBALS['TL_LANG']['MSC']['expandNode'] . '"
				data-contao--limit-height-collapse-value="' . $GLOBALS['TL_LANG']['MSC']['collapseNode'] . '"
				data-contao--limit-height-expand-all-value="' . $GLOBALS['TL_LANG']['DCA']['expandNodes'][0] . '"
				data-contao--limit-height-expand-all-title-value="' . $GLOBALS['TL_LANG']['DCA']['expandNodes'][1] . '"
				data-contao--limit-height-collapse-all-value="' . $GLOBALS['TL_LANG']['DCA']['collapseNodes'][0] . '"
				data-contao--limit-height-collapse-all-title-value="' . $GLOBALS['TL_LANG']['DCA']['collapseNodes'][0] . '"
			>' . $return . '</div>';
		}

		return $return;
	}

    public function delete($blnDoNotRedirect = false)
    {
        // Configure DC to backup variants and languages
        $this->ptable = null;
        $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] = DataContainer::MODE_TREE;

        parent::delete($blnDoNotRedirect);
    }

    /**
	 * Set ptable to current table so reviseTable works like in tree mode (delete variants and translations).
	 */
	protected function reviseTable()
	{
		$GLOBALS['TL_DCA'][$this->strTable]['config']['ptable'] = $this->strTable;

		parent::reviseTable();

		unset($GLOBALS['TL_DCA'][$this->strTable]['config']['ptable']);
	}

	/**
	 * Return a select menu that allows to sort results by a particular field
	 *
	 * @return string
	 */
	protected function sortMenu()
	{
		if (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) != self::MODE_SORTABLE && ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) != self::MODE_PARENT)
		{
			return '';
		}

		$sortingFields = array();

		// Get sorting fields
		foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'] as $k=>$v)
		{
			if ($v['sorting'] ?? null)
			{
				if (\in_array($v['flag'] ?? null, array(self::SORT_INITIAL_LETTER_BOTH, self::SORT_INITIAL_LETTERS_BOTH, self::SORT_DAY_BOTH, self::SORT_MONTH_BOTH, self::SORT_YEAR_BOTH, self::SORT_BOTH)))
				{
					$sortingFields[] = $k . ' DESC';
					$sortingFields[] = $k . ' ASC';
				}
				else
				{
					$sortingFields[] = $k;
				}
			}
		}

		// Return if there are no sorting fields
		if (empty($sortingFields))
		{
			return '';
		}

		/** @var AttributeBagInterface $objSessionBag */
		$objSessionBag = System::getContainer()->get('request_stack')->getSession()->getBag('contao_backend');
		$session = $objSessionBag->all();
		$sessionKey = Input::get('id') ? $this->strTable . '_' . $this->intCurrentPid : $this->strTable;
		$orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
		$firstOrderBy = preg_replace('/\s+.*$/', '', $orderBy[0]);

		// Add PID to order fields
		if (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) == self::MODE_SORTED_PARENT && Database::getInstance()->fieldExists('pid', $this->strTable))
		{
			array_unshift($orderBy, 'pid');
		}

		// Set sorting from user input
		if (Input::post('FORM_SUBMIT') == 'tl_filters')
		{
			$strSort = Input::post('tl_sort');

			// Validate the user input (thanks to aulmn) (see #4971)
			if (\in_array($strSort, $sortingFields, true))
			{
				$session['sorting'][$sessionKey] = \in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$strSort]['flag'] ?? null, array(self::SORT_INITIAL_LETTER_DESC, self::SORT_INITIAL_LETTERS_DESC, self::SORT_DAY_DESC, self::SORT_MONTH_DESC, self::SORT_YEAR_DESC, self::SORT_DESC)) ? "$strSort DESC" : $strSort;
				$objSessionBag->replace($session);
			}
		}

		// Overwrite the "orderBy" value with the session value
		elseif (isset($session['sorting'][$sessionKey]))
		{
			$overwrite = preg_quote(preg_replace('/\s+.*$/', '', $session['sorting'][$sessionKey]), '/');
			$orderBy = array_diff($orderBy, preg_grep('/^' . $overwrite . '/i', $orderBy));

			array_unshift($orderBy, $session['sorting'][$sessionKey]);

			$this->firstOrderBy = $overwrite;
			$this->orderBy = $orderBy;
		}

		$options_sorter = array();

		// Sorting fields
		foreach ($sortingFields as $value)
		{
			$field = str_replace(array(' ASC', ' DESC'), '', $value);
			$options_label = ($lbl = \is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'] ?? null) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] : ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'] ?? null)) ? $lbl : $GLOBALS['TL_LANG']['MSC'][$field] ?? $field;

			if (\is_array($options_label))
			{
				$options_label = $options_label[0];
			}

			if (str_ends_with($value, ' ASC'))
			{
				$sortKey = $options_label . '|ASC';
				$sessionValue = $session['sorting'][$sessionKey] ?? '';
				$aria_label = \sprintf($GLOBALS['TL_LANG']['MSC']['list_orderBy'], $options_label . ' ' . $GLOBALS['TL_LANG']['MSC']['ascending']);
				$options_label .= ' ↑';
			}
			elseif (str_ends_with($value, ' DESC'))
			{
				$sortKey = $options_label . '|DESC';
				$sessionValue = $session['sorting'][$sessionKey] ?? '';
				$aria_label = \sprintf($GLOBALS['TL_LANG']['MSC']['list_orderBy'], $options_label . ' ' . $GLOBALS['TL_LANG']['MSC']['descending']);
				$options_label .= ' ↓';
			}
			else
			{
				$sortKey = $options_label;
				$sessionValue = str_replace(' DESC', '', $session['sorting'][$sessionKey] ?? '');
				$aria_label = \sprintf($GLOBALS['TL_LANG']['MSC']['list_orderBy'], $options_label);
			}

			$options_sorter[$sortKey] = '  <option value="' . StringUtil::specialchars($value) . '"' . (((!isset($session['sorting'][$this->strTable]) && $field == $firstOrderBy) || $value == $sessionValue) ? ' selected="selected"' : '') . ' aria-label="' . $aria_label . '">' . $options_label . '</option>';
		}

		// Sort by option values
		uksort($options_sorter, static function ($a, $b) {
			$a = (new UnicodeString($a))->folded();
			$b = (new UnicodeString($b))->folded();

			if ($a->toString() === $b->toString())
			{
				return 0;
			}

			return strnatcmp($a->ascii()->toString(), $b->ascii()->toString());
		});

		return '
<div class="tl_sorting tl_subpanel">
<strong>' . $GLOBALS['TL_LANG']['MSC']['sortBy'] . ':</strong>
<select name="tl_sort" id="tl_sort" class="tl_select tl_chosen">
' . implode("\n", $options_sorter) . '
</select>
</div>';
	}

	/**
	 * Override search menu to use a different key in the session for variant options.
	 *
	 * @return string
	 */
	protected function searchMenu()
	{
		$searchFields = array('id');

		/** @var AttributeBagInterface $objSessionBag */
		$objSessionBag = System::getContainer()->get('request_stack')->getSession()->getBag('contao_backend');
		$session = $objSessionBag->all();
		$sessionKey = Input::get('id') ? $this->strTable . '_' . $this->intCurrentPid : $this->strTable;

		// Get search fields
		foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'] as $k=>$v)
		{
			if ($v['search'] ?? null)
			{
				$searchFields[] = $k;
			}
		}

		// Return if there are no search fields
		if (empty($searchFields))
		{
			return '';
		}

		// Store search value in the current session
		if (Input::post('FORM_SUBMIT') == 'tl_filters')
		{
			$strField = Input::post('tl_field', true);
			$strKeyword = ltrim(Input::postRaw('tl_value'), '*');

			if ($strField && !\in_array($strField, $searchFields, true))
			{
				$strField = '';
				$strKeyword = '';
			}

			$session['search'][$sessionKey]['field'] = $strField;
			$session['search'][$sessionKey]['value'] = $strKeyword;

			$objSessionBag->replace($session);
		}

		// Set the search value from the session
		elseif (isset($session['search'][$sessionKey]['value']) && (string) $session['search'][$sessionKey]['value'] !== '')
		{
			$searchValue = $session['search'][$sessionKey]['value'];
			$fld = $session['search'][$sessionKey]['field'] ?? null;

			try
			{
				Database::getInstance()->prepare("SELECT '' REGEXP ?")->execute($searchValue);
			}
			catch (DriverException $exception)
			{
				// Quote search string if it is not a valid regular expression
				$searchValue = preg_quote($searchValue, null);
			}

			$strReplacePrefix = '';
			$strReplaceSuffix = '';

			// Decode HTML entities to make them searchable
			if (empty($GLOBALS['TL_DCA'][$this->strTable]['fields'][$fld]['eval']['decodeEntities']))
			{
				$arrReplace = array(
					'&#35;' => '#',
					'&#60;' => '<',
					'&#62;' => '>',
					'&lt;' => '<',
					'&gt;' => '>',
					'&#40;' => '(',
					'&#41;' => ')',
					'&#92;' => '\\\\',
					'&#61;' => '=',
					'&amp;' => '&',
				);

				$strReplacePrefix = str_repeat('REPLACE(', \count($arrReplace));

				foreach ($arrReplace as $strSource => $strTarget)
				{
					$strReplaceSuffix .= ", '$strSource', '$strTarget')";
				}
			}

			$strPattern = "$strReplacePrefix LOWER(CAST(%s AS CHAR)) $strReplaceSuffix REGEXP LOWER(?)";

			if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$fld]['foreignKey']))
			{
				list($t, $f) = explode('.', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$fld]['foreignKey'], 2);
				$this->procedure[] = "(" . \sprintf($strPattern, Database::quoteIdentifier($fld)) . " OR " . \sprintf($strPattern, "(SELECT " . Database::quoteIdentifier($f) . " FROM $t WHERE $t.id=" . $this->strTable . "." . Database::quoteIdentifier($fld) . ")") . ")";
				$this->values[] = $searchValue;
			}
			else
			{
				$this->procedure[] = \sprintf($strPattern, Database::quoteIdentifier($fld));
			}

			$this->values[] = $searchValue;
		}

		$options_sorter = array();

		foreach ($searchFields as $field)
		{
			$option_label = $field;

			if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label']))
			{
				$option_label = \is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label']) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'];
			}
			elseif (isset($GLOBALS['TL_LANG']['MSC'][$field]))
			{
				$option_label = \is_array($GLOBALS['TL_LANG']['MSC'][$field]) ? $GLOBALS['TL_LANG']['MSC'][$field][0] : $GLOBALS['TL_LANG']['MSC'][$field];
			}

			$options_sorter[$option_label . '_' . $field] = '  <option value="' . StringUtil::specialchars($field) . '"' . ((($session['search'][$sessionKey]['field'] ?? $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['defaultSearchField'] ?? null) == $field) ? ' selected="selected"' : '') . '>' . $option_label . '</option>';
		}

		// Sort by option values
		uksort($options_sorter, static function ($a, $b) {
			$a = (new UnicodeString($a))->folded();
			$b = (new UnicodeString($b))->folded();

			if ($a->toString() === $b->toString())
			{
				return 0;
			}

			return strnatcmp($a->ascii()->toString(), $b->ascii()->toString());
		});

		$active = isset($session['search'][$sessionKey]['value']) && (string) $session['search'][$sessionKey]['value'] !== '';

		return '
<div class="tl_search tl_subpanel">
<strong>' . $GLOBALS['TL_LANG']['MSC']['search'] . ':</strong>
<select name="tl_field" class="tl_select tl_chosen' . ($active ? ' active' : '') . '">
' . implode("\n", $options_sorter) . '
</select>
<span>=</span>
<input type="search" name="tl_value" class="tl_text' . ($active ? ' active' : '') . '" value="' . StringUtil::specialchars($session['search'][$sessionKey]['value'] ?? '') . '">
</div>';
	}

	/**
	 * Return a select menu to limit results
	 *
	 * @param boolean $blnOptional
	 *
	 * @return string
	 */
	protected function limitMenu($blnOptional=false)
	{
		/** @var AttributeBagInterface $objSessionBag */
		$objSessionBag = System::getContainer()->get('request_stack')->getSession()->getBag('contao_backend');
		$session = $objSessionBag->all();

		$filter = Input::get('id') ? $this->strTable . '_' . $this->intCurrentPid : $this->strTable;
		$fields = '';

		// Set limit from user input
		if (\in_array(Input::post('FORM_SUBMIT'), array('tl_filters', 'tl_filters_limit')))
		{
			$strLimit = Input::post('tl_limit');

			if ($strLimit == 'tl_limit')
			{
				unset($session['filter'][$filter]['limit']);
			}
			// Validate the user input (thanks to aulmn) (see #4971)
			elseif ($strLimit == 'all' || preg_match('/^[0-9]+,[0-9]+$/', $strLimit))
			{
				$session['filter'][$filter]['limit'] = $strLimit;
			}

			$objSessionBag->replace($session);

			if (Input::post('FORM_SUBMIT') == 'tl_filters_limit')
			{
				$this->reload();
			}
		}

		// Set limit from table configuration
		else
		{
			$this->limit = isset($session['filter'][$filter]['limit']) ? (($session['filter'][$filter]['limit'] == 'all') ? null : $session['filter'][$filter]['limit']) : '0,' . Config::get('resultsPerPage');

			$arrProcedure = $this->procedure;
			$arrValues = $this->values;
			$query = "SELECT COUNT(*) AS count FROM " . $this->strTable;

			if (Input::get('id')) {
				$arrProcedure[] = "pid=?";
				$arrValues[] = Input::get('id');
			} else {
				$arrProcedure[] = "pid=0";
			}

			if (!empty($this->root) && \is_array($this->root))
			{
				$arrProcedure[] = 'id IN(' . implode(',', $this->root) . ')';
			}

			if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dynamicPtable'] ?? null)
			{
				$arrProcedure[] = 'ptable=?';
				$arrValues[] = $this->ptable;
			}

			if (!empty($arrProcedure))
			{
				$query .= " WHERE " . implode(' AND ', $arrProcedure);
			}

			$objTotal = Database::getInstance()->prepare($query)->execute(...$arrValues);
			$this->total = $objTotal->count;
			$options_total = 0;
			$maxResultsPerPage = Config::get('maxResultsPerPage');
			$blnIsMaxResultsPerPage = false;

			// Overall limit
			if ($maxResultsPerPage > 0 && $this->total > $maxResultsPerPage && ($this->limit === null || preg_replace('/^.*,/', '', $this->limit) == $maxResultsPerPage))
			{
				if ($this->limit === null)
				{
					$this->limit = '0,' . Config::get('maxResultsPerPage');
				}

				$blnIsMaxResultsPerPage = true;
				Config::set('resultsPerPage', Config::get('maxResultsPerPage'));
				$session['filter'][$filter]['limit'] = Config::get('maxResultsPerPage');
			}

			$options = '';

			// Build options
			if ($this->total > 0)
			{
				$options_total = ceil($this->total / Config::get('resultsPerPage'));

				// Reset limit if other parameters have decreased the number of results
				if ($this->limit !== null && (!$this->limit || preg_replace('/,.*$/', '', $this->limit) > $this->total))
				{
					$this->limit = '0,' . Config::get('resultsPerPage');
				}

				// Build options
				for ($i=0; $i<$options_total; $i++)
				{
					$this_limit = ($i*Config::get('resultsPerPage')) . ',' . Config::get('resultsPerPage');
					$upper_limit = $i*Config::get('resultsPerPage')+Config::get('resultsPerPage');

					if ($upper_limit > $this->total)
					{
						$upper_limit = $this->total;
					}

					$options .= '
  <option value="' . $this_limit . '"' . Widget::optionSelected($this->limit, $this_limit) . '>' . ($i*Config::get('resultsPerPage')+1) . ' - ' . $upper_limit . '</option>';
				}

				if (!$blnIsMaxResultsPerPage)
				{
					$options .= '
  <option value="all"' . Widget::optionSelected($this->limit, null) . '>' . $GLOBALS['TL_LANG']['MSC']['filterAll'] . '</option>';
				}
			}

			// Return if there is only one page
			if ($blnOptional && ($this->total < 1 || $options_total < 2))
			{
				return '';
			}

			$fields = '
<select name="tl_limit" class="tl_select tl_chosen' . (($session['filter'][$filter]['limit'] ?? null) != 'all' && $this->total > Config::get('resultsPerPage') ? ' active' : '') . '" onchange="this.form.submit()">
  <option value="tl_limit">' . $GLOBALS['TL_LANG']['MSC']['filterRecords'] . '</option>' . $options . '
</select> ';
		}

		return '
<div class="tl_limit tl_subpanel">
<strong>' . $GLOBALS['TL_LANG']['MSC']['showOnly'] . ':</strong> ' . $fields . '
</div>';
	}

	/**
	 * Override the parent method to override the session key.
	 *
	 * @param integer $intFilterPanel
	 *
	 * @return string
	 */
	protected function filterMenu($intFilterPanel)
	{
		/** @var AttributeBagInterface $objSessionBag */
		$objSessionBag = \Contao\System::getContainer()->get('request_stack')->getSession()->getBag('contao_backend');

		$fields = '';
		$sortingFields = array();
		$session = $objSessionBag->all();
		$filter = Input::get('id') ? $this->strTable . '_' . $this->intCurrentPid : $this->strTable;

		// Get the sorting fields
		foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'] as $k=>$v)
		{
			if (($v['filter'] ?? null) == $intFilterPanel)
			{
				$sortingFields[] = $k;
			}
		}

		// Return if there are no sorting fields
		if (empty($sortingFields))
		{
			return '';
		}

		// Set filter from user input
		if (Input::post('FORM_SUBMIT') == 'tl_filters')
		{
			foreach ($sortingFields as $field)
			{
				if (Input::post($field, true) != 'tl_' . $field)
				{
					$session['filter'][$filter][$field] = Input::post($field, true);
				}
				else
				{
					unset($session['filter'][$filter][$field]);
				}
			}

			$objSessionBag->replace($session);
		}

		// Set filter from table configuration
		else
		{
			foreach ($sortingFields as $field)
			{
				$what = Database::quoteIdentifier($field);

				if (isset($session['filter'][$filter][$field]))
				{
					// Sort by day
					if (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] ?? null, array(5, 6)))
					{
						if (!$session['filter'][$filter][$field])
						{
							$this->procedure[] = $what . "=''";
						}
						else
						{
							$objDate = new Date($session['filter'][$filter][$field]);
							$this->procedure[] = $what . ' BETWEEN ? AND ?';
							$this->values[] = $objDate->dayBegin;
							$this->values[] = $objDate->dayEnd;
						}
					}

					// Sort by month
					elseif (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] ?? null, array(7, 8)))
					{
						if (!$session['filter'][$filter][$field])
						{
							$this->procedure[] = $what . "=''";
						}
						else
						{
							$objDate = new Date($session['filter'][$filter][$field]);
							$this->procedure[] = $what . ' BETWEEN ? AND ?';
							$this->values[] = $objDate->monthBegin;
							$this->values[] = $objDate->monthEnd;
						}
					}

					// Sort by year
					elseif (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] ?? null, array(9, 10)))
					{
						if (!$session['filter'][$filter][$field])
						{
							$this->procedure[] = $what . "=''";
						}
						else
						{
							$objDate = new Date($session['filter'][$filter][$field]);
							$this->procedure[] = $what . ' BETWEEN ? AND ?';
							$this->values[] = $objDate->yearBegin;
							$this->values[] = $objDate->yearEnd;
						}
					}

					// Manual filter
					elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['multiple'] ?? null)
					{
						// CSV lists (see #2890)
						if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['csv']))
						{
							$this->procedure[] = $this->Database->findInSet('?', $field, true);
							$this->values[] = $session['filter'][$filter][$field] ?? null;
						}
						else
						{
							$this->procedure[] = $what . ' LIKE ?';
							$this->values[] = '%"' . $session['filter'][$filter][$field] . '"%';
						}
					}

					// Other sort algorithm
					else
					{
						$this->procedure[] = $what . '=?';
						$this->values[] = $session['filter'][$filter][$field] ?? null;
					}
				}
			}
		}

		// Add sorting options
		foreach ($sortingFields as $cnt=>$field)
		{
			$arrValues = array();
			$arrProcedure = array();

			if (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) == 4)
			{
				$arrProcedure[] = 'pid=?';
				$arrValues[] = $this->intCurrentPid;
			}

			if (!empty($this->root) && \is_array($this->root))
			{
				$arrProcedure[] = "id IN(" . implode(',', array_map('\intval', $this->root)) . ")";
			}

			// Check for a static filter (see #4719)
			if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['filter'] ?? null))
			{
				foreach ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['filter'] as $fltr)
				{
					if (\is_string($fltr))
					{
						$arrProcedure[] = $fltr;
					}
					else
					{
						$arrProcedure[] = $fltr[0];
						$arrValues[] = $fltr[1];
					}
				}
			}

			// Support empty ptable fields
			if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dynamicPtable'] ?? null)
			{
				$arrProcedure[] = ($this->ptable == 'tl_article') ? "(ptable=? OR ptable='')" : "ptable=?";
				$arrValues[] = $this->ptable;
			}

			$what = Database::quoteIdentifier($field);

			// Optimize the SQL query (see #8485)
			if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag']))
			{
				// Sort by day
				if (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], array(5, 6)))
				{
					$what = "IF($what!='', FLOOR(UNIX_TIMESTAMP(FROM_UNIXTIME($what , '%%Y-%%m-%%d'))), '') AS $what";
				}

				// Sort by month
				elseif (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], array(7, 8)))
				{
					$what = "IF($what!='', FLOOR(UNIX_TIMESTAMP(FROM_UNIXTIME($what , '%%Y-%%m-01'))), '') AS $what";
				}

				// Sort by year
				elseif (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], array(9, 10)))
				{
					$what = "IF($what!='', FLOOR(UNIX_TIMESTAMP(FROM_UNIXTIME($what , '%%Y-01-01'))), '') AS $what";
				}
			}

			$table = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6) ? $this->ptable : $this->strTable;

			// Limit the options if there are root records
			if (isset($GLOBALS['TL_DCA'][$table]['list']['sorting']['root']) && $GLOBALS['TL_DCA'][$table]['list']['sorting']['root'] !== false)
			{
				$rootIds = array_map('\intval', $GLOBALS['TL_DCA'][$table]['list']['sorting']['root']);

				// Also add the child records of the table (see #1811)
				if (($GLOBALS['TL_DCA'][$table]['list']['sorting']['mode'] ?? null) == 5)
				{
					$rootIds = array_merge($rootIds, $this->Database->getChildRecords($rootIds, $table));
				}

				if (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) == 6)
				{
					$arrProcedure[] = "pid IN(" . implode(',', $rootIds) . ")";
				}
				else
				{
					$arrProcedure[] = "id IN(" . implode(',', $rootIds) . ")";
				}
			}

			$objFields = $this->Database->prepare("SELECT DISTINCT " . $what . " FROM " . $this->strTable . ((\is_array($arrProcedure) && isset($arrProcedure[0])) ? ' WHERE ' . implode(' AND ', $arrProcedure) : ''))
										->execute(...$arrValues);

			// Begin select menu
			$fields .= '
<select name="' . $field . '" id="' . $field . '" class="tl_select tl_chosen' . (isset($session['filter'][$filter][$field]) ? ' active' : '') . '">
  <option value="tl_' . $field . '">' . (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'] ?? null) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] : ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'] ?? null)) . '</option>
  <option value="tl_' . $field . '">---</option>';

			if ($objFields->numRows)
			{
				$options = $objFields->fetchEach($field);

				// Sort by day
				if (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] ?? null, array(5, 6)))
				{
					($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] ?? null) == 6 ? rsort($options) : sort($options);

					foreach ($options as $k=>$v)
					{
						if ($v === '')
						{
							$options[$v] = '-';
						}
						else
						{
							$options[$v] = Date::parse(Config::get('dateFormat'), $v);
						}

						unset($options[$k]);
					}
				}

				// Sort by month
				elseif (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] ?? null, array(7, 8)))
				{
					($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] ?? null) == 8 ? rsort($options) : sort($options);

					foreach ($options as $k=>$v)
					{
						if ($v === '')
						{
							$options[$v] = '-';
						}
						else
						{
							$options[$v] = date('Y-m', $v);
							$intMonth = (date('m', $v) - 1);

							if (isset($GLOBALS['TL_LANG']['MONTHS'][$intMonth]))
							{
								$options[$v] = $GLOBALS['TL_LANG']['MONTHS'][$intMonth] . ' ' . date('Y', $v);
							}
						}

						unset($options[$k]);
					}
				}

				// Sort by year
				elseif (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] ?? null, array(9, 10)))
				{
					($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] ?? null) == 10 ? rsort($options) : sort($options);

					foreach ($options as $k=>$v)
					{
						if ($v === '')
						{
							$options[$v] = '-';
						}
						else
						{
							$options[$v] = date('Y', $v);
						}

						unset($options[$k]);
					}
				}

				// Manual filter
				if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['multiple'] ?? null)
				{
					$moptions = array();

					// TODO: find a more effective solution
					foreach ($options as $option)
					{
						// CSV lists (see #2890)
						if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['csv']))
						{
							$doptions = StringUtil::trimsplit($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['csv'], $option);
						}
						else
						{
							$doptions = StringUtil::deserialize($option);
						}

						if (\is_array($doptions))
						{
							$moptions = array_merge($moptions, $doptions);
						}
					}

					$options = $moptions;
				}

				$options = array_unique($options);
				$options_callback = array();

				// Call the options_callback
				if (!($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference'] ?? null) && (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback'] ?? null) || \is_callable($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback'] ?? null)))
				{
					if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback'] ?? null))
					{
						$strClass = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback'][0];
						$strMethod = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback'][1];

						$this->import($strClass);
						$options_callback = $this->$strClass->$strMethod($this);
					}
					elseif (\is_callable($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback'] ?? null))
					{
						$options_callback = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback']($this);
					}

					// Sort options according to the keys of the callback array
					$options = array_intersect(array_keys($options_callback), $options);
				}

				$options_sorter = array();
				$blnDate = \in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] ?? null, array(5, 6, 7, 8, 9, 10));

				// Options
				foreach ($options as $kk=>$vv)
				{
					$value = $blnDate ? $kk : $vv;

					// Options callback
					if (!empty($options_callback) && \is_array($options_callback))
					{
						$vv = $options_callback[$vv];
					}

					// Replace the ID with the foreign key
					elseif (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['foreignKey']))
					{
						$key = explode('.', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['foreignKey'], 2);

						$objParent = $this->Database->prepare("SELECT " . Database::quoteIdentifier($key[1]) . " AS value FROM " . $key[0] . " WHERE id=?")
													->limit(1)
													->execute($vv);

						if ($objParent->numRows)
						{
							$vv = $objParent->value;
						}
					}

					// Replace boolean checkbox value with "yes" and "no"
					elseif (($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['isBoolean'] ?? null) || (($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['inputType'] ?? null) == 'checkbox' && !($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['multiple'] ?? null)))
					{
						$vv = $vv ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
					}

					// Get the name of the parent record (see #2703)
					elseif ($field == 'pid')
					{
						$this->loadDataContainer($this->ptable);
						$showFields = $GLOBALS['TL_DCA'][$this->ptable]['list']['label']['fields'] ?? array();

						if (!($showFields[0] ?? null))
						{
							$showFields[0] = 'id';
						}

						$objShowFields = $this->Database->prepare("SELECT " . Database::quoteIdentifier($showFields[0]) . " FROM " . $this->ptable . " WHERE id=?")
														->limit(1)
														->execute($vv);

						if ($objShowFields->numRows)
						{
							$vv = $objShowFields->{$showFields[0]};
						}
					}

					$option_label = '';

					// Use reference array
					if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference']))
					{
						$option_label = \is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference'][$vv] ?? null) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference'][$vv][0] : ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference'][$vv] ?? null);
					}

					// Associative array
					elseif (($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['isAssociative'] ?? null) || \Contao\ArrayUtil::isAssoc($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options'] ?? null))
					{
						$option_label = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options'][$vv] ?? null;
					}

					// No empty options allowed
					if (!$option_label)
					{
						if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['foreignKey']))
					{
						$option_label = $vv ?: '-';
					}
						else
						{
							$option_label = (string) $vv !== '' ? $vv : '-';
						}
					}

					$options_sorter[$option_label . '_' . $field] = '  <option value="' . StringUtil::specialchars($value) . '"' . ((isset($session['filter'][$filter][$field]) && $value == $session['filter'][$filter][$field]) ? ' selected="selected"' : '') . '>' . StringUtil::specialchars($option_label) . '</option>';
				}

				// Sort by option values
				if (!$blnDate)
				{
					uksort($options_sorter, static function ($a, $b)
					{
						$a = (new UnicodeString($a))->folded();
						$b = (new UnicodeString($b))->folded();

						if ($a->toString() === $b->toString())
						{
							return 0;
						}

						return strnatcmp($a->ascii()->toString(), $b->ascii()->toString());
					});

					if (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] ?? null, array(2, 4, 12)))
					{
						$options_sorter = array_reverse($options_sorter, true);
					}
				}

				$fields .= "\n" . implode("\n", array_values($options_sorter));
			}

			// End select menu
			$fields .= '
</select> ';

			// Force a line-break after six elements (see #3777)
			if ((($cnt + 1) % 6) == 0)
			{
				$fields .= '<br>';
			}
		}

		return '
<div class="tl_filter tl_subpanel">
<strong>' . $GLOBALS['TL_LANG']['MSC']['filter'] . ':</strong> ' . $fields . '
</div>';
	}

	/**
	 * Copy multilingual fields from fallback to current language
	 */
	public function copyFallback()
	{
		/** @var AttributeBagInterface $objSessionBag */
		$objSessionBag = \Contao\System::getContainer()->get('request_stack')->getSession()->getBag('contao_backend');

		$session = $objSessionBag->all();
		$strLanguage = $session['language'][$this->strTable][$this->intId] ?? null;
		$arrDuplicate = array();

		foreach (StringUtil::trimsplit('[;,]', $this->getPalette()) as $field)
		{
			if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field] ?? null) && ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['multilingual'] ?? null)) {
				$arrDuplicate[] = $field;
			}
		}

		if (!empty($arrDuplicate))
		{
			$language = $this->Database->prepare("SELECT id FROM {$this->strTable} WHERE pid=? AND language=?")->execute($this->intId, $strLanguage);

			if ($language->numRows)
			{
				$objVersions = new Versions($this->strTable, $language->id);
				$objVersions->initialize();

				$arrRow = $this->Database->prepare('SELECT '. implode(',', $arrDuplicate) . " FROM {$this->strTable} WHERE id=?")->execute($this->intId)->fetchAssoc();
				$this->Database->prepare("UPDATE {$this->strTable} %s WHERE id=?")->set($arrRow)->execute($language->id);

				$objVersions->create();

				\Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::INFO, sprintf('A new version of record ID %s (table %s) has been created', $language->id, $this->strTable), ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::GENERAL)]);
			}
		}

		Controller::redirect(Backend::addToUrl('act=edit'));
	}
}

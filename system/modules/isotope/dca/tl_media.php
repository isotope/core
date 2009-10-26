<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Winans Creative 2009
 * @author     Fred Bliss <fred@winanscreative.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Table tl_media
 */
$GLOBALS['TL_DCA']['tl_media'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'MediaUpload',
		'onload_callback' => array
		(
			array('tl_media', 'checkPermission')
		)
	),

	// List
	'list' => array
	(
		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'toggleNodes' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['toggleNodes'],
				'href'                => 'tg=all',
				'class'               => 'header_toggle'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_media']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif',
				'button_callback'     => array('tl_media', 'editFile')
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_media']['copy'],
				'href'                => 'act=paste&amp;mode=copy',
				'icon'                => 'copy.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();"',
				'button_callback'     => array('tl_media', 'copyFile')
			),
			'cut' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_media']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();"',
				'button_callback'     => array('tl_media', 'cutFile')
			),
			'source' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_media']['source'],
				'href'                => 'act=source',
				'icon'                => 'editor.gif',
				'button_callback'     => array('tl_media', 'editSource')
			),
			'protect' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_media']['protect'],
				'href'                => 'act=protect',
				'icon'                => 'protect.gif',
				'button_callback'     => array('tl_media', 'protectFolder')
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_media']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
				'button_callback'     => array('tl_media', 'deleteFile')
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => 'name'
	),

	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_media']['name'],
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'decodeEntities'=>true)
		)
	)
);



/**
 * tl_media class.
 * 
 * @extends Backend
 */
class tl_media extends Backend
{
	/**
	 * Import the back end user object.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}


	/**
	 * Check permissions to edit the file system.
	 * 
	 * @access public
	 * @return void
	 */
	public function checkPermission()
	{
		if ($this->User->isAdmin)
		{
			return;
		}

		// Set mediamounts
		$GLOBALS['TL_DCA']['tl_media']['list']['sorting']['root'] = $this->User->mediamounts;

		// Disable upload button if uploads are not allowed
		if (!is_array($this->User->fop) || !in_array('f1', $this->User->fop))
		{
			$GLOBALS['TL_DCA']['tl_media']['config']['closed'] = true;
		}

		// Disable edit_all button
		if (!is_array($this->User->fop) || !in_array('f2', $this->User->fop))
		{
			$GLOBALS['TL_DCA']['tl_media']['config']['notEditable'] = true;

			if ($this->Input->get('act') == 'editAll')
			{
				$session = $this->Session->getData();
				$session['CURRENT']['IDS'] = array();
				$this->Session->setData($session);
			}
		}

		// Set allowed page IDs (delete all)
		if ($this->Input->get('act') == 'deleteAll')
		{
			$session = $this->Session->getData();

			if (is_array($session['CURRENT']['IDS']))
			{
				$folders = array();
				$delete_all = array();

				foreach ($session['CURRENT']['IDS'] as $id)
				{
					if (is_dir(TL_ROOT . '/' . $id))
					{
						$folders[] = $id;

						if ((in_array('f4', $this->User->fop) && count(scan(TL_ROOT . '/' . $id)) < 1) || in_array('f4', $this->User->fop))
						{
							$delete_all[] = $id;
						}
					}

					elseif ((in_array('f3', $this->User->fop) || in_array('f4', $this->User->fop)) && !in_array(dirname($id), $folders))
					{
						$delete_all[] = $id;
					}
				}

				$session['CURRENT']['IDS'] = $delete_all;
				$this->Session->setData($session);
			}
		}

		// Check current action
		if ($this->Input->get('act') && $this->Input->get('act') != 'paste')
		{
			// No permissions at all
			if (!is_array($this->User->fop))
			{
				$this->log('No permission to manipulate files', 'tl_media checkPermission()', TL_ERROR);
				$this->redirect('typolight/main.php?act=error');
			}

			// Upload permission
			if ($this->Input->get('act') == 'move' && !in_array('f1', $this->User->fop))
			{
				$this->log('No permission to upload files', 'tl_media checkPermission()', TL_ERROR);
				$this->redirect('typolight/main.php?act=error');
			}

			// New, edit, copy or cut permission
			if (in_array($this->Input->get('act'), array('create', 'edit', 'copy', 'cut')) && !in_array('f2', $this->User->fop))
			{
				$this->log('No permission to create, edit, copy or move files', 'tl_media checkPermission()', TL_ERROR);
				$this->redirect('typolight/main.php?act=error');
			}

			// Delete permission
			if ($this->Input->get('act') == 'delete')
			{
				// Folders
				if (is_dir(TL_ROOT . '/' . $this->Input->get('id')))
				{
					$files = scan(TL_ROOT . '/' . $this->Input->get('id'));

					if (count($files) && !in_array('f4', $this->User->fop))
					{
						$this->log('No permission to delete folder "'.$this->Input->get('id').'" recursively', 'tl_media checkPermission()', TL_ERROR);
						$this->redirect('typolight/main.php?act=error');
					}

					elseif (!in_array('f3', $this->User->fop))
					{
						$this->log('No permission to delete folder "'.$this->Input->get('id').'"', 'tl_media checkPermission()', TL_ERROR);
						$this->redirect('typolight/main.php?act=error');
					}
				}

				// Files
				elseif (!in_array('f3', $this->User->fop))
				{
					$this->log('No permission to delete file "'.$this->Input->get('id').'"', 'tl_media checkPermission()', TL_ERROR);
					$this->redirect('typolight/main.php?act=error');
				}
			}
		}
	}


	/**
	 * Return the edit file button.
	 * 
	 * @access public
	 * @param array $row
	 * @param string $href
	 * @param string $label
	 * @param string $title
	 * @param string $icon
	 * @param string $attributes
	 * @return string
	 */
	public function editFile($row, $href, $label, $title, $icon, $attributes)
	{
		return ($this->User->isAdmin || is_array($this->User->fop) && in_array('f2', $this->User->fop)) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}


	/**
	 * Return the copy file button.
	 * 
	 * @access public
	 * @param array $row
	 * @param string $href
	 * @param string $label
	 * @param string $title
	 * @param string $icon
	 * @param string $attributes
	 * @return string
	 */
	public function copyFile($row, $href, $label, $title, $icon, $attributes)
	{
		return ($this->User->isAdmin || is_array($this->User->fop) && in_array('f2', $this->User->fop)) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}


	/**
	 * Return the cut file button.
	 * 
	 * @access public
	 * @param array $row
	 * @param string $href
	 * @param string $label
	 * @param string $title
	 * @param string $icon
	 * @param string $attributes
	 * @return string
	 */
	public function cutFile($row, $href, $label, $title, $icon, $attributes)
	{
		return ($this->User->isAdmin || is_array($this->User->fop) && in_array('f2', $this->User->fop)) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}


	/**
	 * Return the delete file button.
	 * 
	 * @access public
	 * @param array $row
	 * @param string $href
	 * @param string $label
	 * @param string $title
	 * @param string $icon
	 * @param string $attributes
	 * @return string
	 */
	public function deleteFile($row, $href, $label, $title, $icon, $attributes)
	{
		if (is_dir(TL_ROOT . '/' . $row['id']) && count(scan(TL_ROOT . '/' . $row['id'])))
		{
			return ($this->User->isAdmin || is_array($this->User->fop) && in_array('f4', $this->User->fop)) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
		}

		return ($this->User->isAdmin || is_array($this->User->fop) && (in_array('f3', $this->User->fop) || in_array('f4', $this->User->fop))) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}


	/**
	 * Return the edit file source button.
	 * 
	 * @access public
	 * @param array $row
	 * @param string $href
	 * @param string $label
	 * @param string $title
	 * @param string $icon
	 * @param string $attributes
	 * @return string
	 */
	public function editSource($row, $href, $label, $title, $icon, $attributes)
	{
		if (!$this->User->isAdmin && !in_array('f5', $this->User->fop))
		{
			return '';
		}

		$strDecoded = urldecode($row['id']);

		if (is_dir(TL_ROOT . '/' . $strDecoded))
		{
			return '';
		}
			
		if (!in_array($objFile->extension, trimsplit(',', $GLOBALS['TL_CONFIG']['editableFiles'])))
		{
			
			return $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
		}

		return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
	}


	/**
	 * Return the edit file source button.
	 * 
	 * @access public
	 * @param array $row
	 * @param string $href
	 * @param string $label
	 * @param string $title
	 * @param string $icon
	 * @param string $attributes
	 * @return string
	 */
	public function protectFolder($row, $href, $label, $title, $icon, $attributes)
	{
		$strDecoded = urldecode($row['id']);

		if (!is_dir(TL_ROOT . '/' . $strDecoded))
		{
			return '';
		}

		// Remove protection
		if (count(preg_grep('/^\.htaccess/i', scan(TL_ROOT . '/' . $strDecoded))) > 0)
		{
			$label = $GLOBALS['TL_LANG']['tl_media']['unlock'][0];
			$title = sprintf($GLOBALS['TL_LANG']['tl_media']['unlock'][1], $row['id']);

			return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon), $label).'</a> ';
		}

		// Protect folder
		return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
	}
	
	
	/**
	 * Generate an image tag and return it as HTML string.
	 * 
	 * @access protected
	 * @param string $src
	 * @param string $alt. (default: '')
	 * @param string $attributes. (default: '')
	 * @return string
	 */
	protected function generateImage($src, $alt='', $attributes='')
	{
		if (strpos($src, '/') === false)
		{
			$src = sprintf('system/themes/%s/images/%s', $this->getTheme(), $src);
		}

		if (!file_exists(TL_ROOT . '/'.$src))
		{
			return '';
		}

		$size = getimagesize(TL_ROOT . '/'.$src);

		return '<img src="'.$src.'" '.$size[3].' alt="'.specialchars($alt).'"'.(strlen($attributes) ? ' '.$attributes : '').' />';
	}
}


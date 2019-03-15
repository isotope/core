<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * @copyright  Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Module;

use Contao\Database;
use Contao\FrontendTemplate;
use Contao\FrontendUser;
use Contao\PageModel;
use Contao\StringUtil;
use Haste\Input\Input;
use Haste\Util\Url;
use Isotope\Interfaces\IsotopeFilterModule;
use Isotope\Isotope;
use Isotope\RequestCache\Filter;

class CategoryFilter extends AbstractProductFilter implements IsotopeFilterModule
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_iso_categoryfilter';

    /**
     * Constructor.
     *
     * @param object $objModule
     * @param string $strColumn
     */
    public function __construct($objModule, $strColumn = 'main')
    {
        parent::__construct($objModule, $strColumn);

        // Remove setting to prevent override of the module template
        $this->iso_filterTpl = '';
        $this->navigationTpl = $this->navigationTpl ?: 'nav_default';

        $this->activeFilters = Isotope::getRequestCache()->getFiltersForModules(array($this->id));
    }

    /**
     * Display a wildcard in the back end
     *
     * @return string
     */
    public function generate()
    {
        if ('BE' === TL_MODE) {
            return $this->generateWildcard();
        }

        // Hide product list in reader mode if the respective setting is enabled
        if ($this->iso_hide_list && Input::getAutoItem('product', false, true) != '') {
            return '';
        }

        if (!$this->rootPage) {
            return '';
        }

        return parent::generate();
    }

    /**
     * Compile the module
     */
    protected function compile()
    {
        $currentIds = [];
        $filter = Isotope::getRequestCache()->getFiltersForModules([$this->id])[0];

        if ($filter instanceof Filter && $filter['attribute'] === 'c.page_id') {
            $currentIds = (array) $filter['value'];
        }

        $allIds = [];

        $this->Template->request = ampersand(\Environment::get('indexFreeRequest'));
        $this->Template->skipId = 'skipNavigation' . $this->id;
        $this->Template->skipNavigation = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['skipNavigation']);
        $this->Template->items = $this->renderFilterTree($this->rootPage, 1, $currentIds, $trail, $allIds);

        if ($input = \Input::get('categoryfilter', true)) {
            $arrFilter = explode(';', base64_decode($input), 3);

            if ($arrFilter[0] == $this->id) {
                $this->saveFilter($arrFilter[1], $arrFilter[2], $currentIds, $allIds);
            }
        }
    }

    private function saveFilter($action, $id, array $currentIds, array $allIds)
    {
        $childIds = [(int) $id];
        $childIds = Database::getInstance()->getChildRecords($childIds, 'tl_page', false, $childIds);

        if ('add' === $action) {
            $ids = array_unique(array_merge($currentIds, $childIds));
        } else {
            $page = PageModel::findByPk($id)->loadDetails();
            $ids = array_diff($currentIds, $childIds, $page->trail);
        }

        $ids = array_intersect($ids, $allIds);

        if (empty($ids)) {
            Isotope::getRequestCache()->unsetFiltersForModule($this->id);
        } else {
            $filter = Filter::attribute('c.page_id')->inArray($ids);
            Isotope::getRequestCache()->setFiltersForModule([$filter], $this->id);
        }

        $objCache = Isotope::getRequestCache()->saveNewConfiguration();

        // Include \Environment::base or the URL would not work on the index page
        \Controller::redirect(
            \Environment::get('base') .
            Url::addQueryString(
                'isorc='.$objCache->id,
                Url::removeQueryString(array('categoryfilter'), ($this->jumpTo ?: null))
            )
        );
    }

    private function renderFilterTree($pid, $level = 1, array $currentIds = [], &$hasActive = false, array &$allIds = [])
    {
        // Get all active subpages
        $pages = \PageModel::findPublishedSubpagesWithoutGuestsByPid($pid, $this->showHidden);

        if ($pages === null) {
            return '';
        }

        $items = array();
        $groups = array();

        // Get all groups of the current front end user
        if (FE_USER_LOGGED_IN) {
            $groups = FrontendUser::getInstance()->groups;
        }

        /** @var \PageModel $objPage */
        global $objPage;

        // Browse subpages
        foreach ($pages as $subpage) {
            $subitems = '';
            $trail = false;
            $_groups = deserialize($subpage->groups);

            // Do not show protected pages unless a back end or front end user is logged in
            if ($subpage->protected
                && BE_USER_LOGGED_IN !== true
                && (!\is_array($_groups) || !\count(array_intersect($_groups, $groups)))
                && !$this->showProtected
            ) {
                continue;
            }

            // Check whether there will be subpages
            if ($subpage->subpages > 0
                && (!$this->showLevel
                    || $this->showLevel >= $level
                    || (!$this->hardLimit
                        && ($objPage->id == $subpage->id || \in_array($objPage->id, Database::getInstance()->getChildRecords($subpage->id, 'tl_page')))
                    )
                )
            ) {
                $subitems = $this->renderFilterTree($subpage->id, $level + 1, $currentIds, $trail, $allIds);
            }

            $row = $subpage->row();

            // Active page
            if (\in_array($subpage->id, $currentIds))
            {
                $strClass = 'active';

                $hasActive = true;
                $row['isActive'] = true;
                $row['isTrail'] = false;
            } else {
                $strClass = $trail ? ' trail' : '';

                $row['isActive'] = false;
                $row['isTrail'] = $trail;
            }

            $value = base64_encode($this->id . ';' . ($row['isActive'] ? 'del' : 'add') . ';' . $subpage->id);
            $href  = Url::addQueryString(
                'categoryfilter=' . $value,
                Url::removeQueryStringCallback(function ($value, $key) {
                    return strpos($key, 'page_iso') !== 0;
                })
            );

            $row['subitems'] = $subitems;
            $row['class'] = trim($strClass.($subitems ? ' submenu' : '').($subpage->protected ? ' protected' : '').($subpage->cssClass ? ' '.$subpage->cssClass : ''));
            $row['title'] = specialchars($subpage->title, true);
            $row['pageTitle'] = specialchars($subpage->pageTitle, true);
            $row['link'] = $subpage->title;
            $row['href'] = $href;
            $row['target'] = '';
            $row['description'] = str_replace(array("\n", "\r"), array(' ', ''), $subpage->description);

            $items[] = $row;
            $allIds[] = $subpage->id;

            if ($trail) {
                $hasActive = true;
            }
        }

        // Add classes first and last
        if (!empty($items)) {
            $last = \count($items) - 1;

            $items[0]['class'] = trim($items[0]['class'].' first');
            $items[$last]['class'] = trim($items[$last]['class'].' last');
        } else {
            return '';
        }

        /** @var \FrontendTemplate|object $objTemplate */
        $objTemplate = new FrontendTemplate($this->navigationTpl);

        $objTemplate->pid = $pid;
        $objTemplate->type = \get_class($this);
        $objTemplate->cssID = $this->cssID;
        $objTemplate->level = 'level_'.$level;
        $objTemplate->items = $items;

        return $objTemplate->parse();
    }
}

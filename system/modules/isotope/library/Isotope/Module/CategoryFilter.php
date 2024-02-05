<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Module;

use Contao\Controller;
use Contao\Database;
use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\FrontendUser;
use Contao\PageModel;
use Contao\StringUtil;
use Haste\Input\Input;
use Haste\Util\Url;
use Isotope\CompatibilityHelper;
use Isotope\Interfaces\IsotopeFilterModule;
use Isotope\Isotope;

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
        $this->navigationTpl = $this->navigationTpl ?: 'nav_iso_categoryfilter';

        $this->activeFilters = Isotope::getRequestCache()->getFiltersForModules(array($this->id));
    }

    /**
     * Display a wildcard in the back end
     *
     * @return string
     */
    public function generate()
    {
        if (CompatibilityHelper::isBackend()) {
            return $this->generateWildcard();
        }

        // Hide product list in reader mode if the respective setting is enabled
        if ($this->iso_hide_list && Input::getAutoItem('product', false, true) != '') {
            return '';
        }

        return parent::generate();
    }

    /**
     * Compile the module
     */
    protected function compile()
    {
        // Set the trail and level
        if ($this->defineRoot && $this->rootPage > 0) {
            $trail = [$this->rootPage];
            $level = 0;
        } else {
            /** @var PageModel $objPage */
            global $objPage;

            $trail = $objPage->trail;
            $level = ($this->levelOffset > 0) ? $this->levelOffset : 0;
        }

        $currentIds = [];
        $filter = Isotope::getRequestCache()->getFiltersForModules([$this->id])[0] ?? null;

        if ($filter instanceof \Isotope\RequestCache\CategoryFilter) {
            $currentIds = $filter['value'];
        }

        $allIds = [];

        $this->Template->request = \Contao\StringUtil::ampersand(Environment::get('indexFreeRequest'));
        $this->Template->skipId = 'skipNavigation' . $this->id;
        $this->Template->skipNavigation = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['skipNavigation']);
        $this->Template->items = $this->renderFilterTree($trail[$level], 1, $currentIds, $trail, $allIds);

        if ($input = Input::get('categoryfilter', true)) {
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

        $ids = array_values(array_intersect($ids, $allIds));

        if (empty($ids)) {
            Isotope::getRequestCache()->unsetFiltersForModule($this->id);
        } else {
            $filter = new \Isotope\RequestCache\CategoryFilter($ids);
            Isotope::getRequestCache()->setFiltersForModule([$filter], $this->id);
        }

        $objCache = Isotope::getRequestCache()->saveNewConfiguration();

        // Include Environment::base or the URL would not work on the index page
        Controller::redirect(
            Environment::get('base') .
            Url::addQueryString(
                'isorc='.$objCache->id,
                Url::removeQueryStringCallback(
                    static function ($value, $key) {
                        return 'categoryfilter' !== $key && !str_starts_with($key, 'page_iso');
                    },
                    ($this->jumpTo ?: null)
                )
            )
        );
    }

    private function renderFilterTree($pid, $level = 1, array $currentIds = [], &$hasActive = false, array &$allIds = [])
    {
        // Get all active subpages
        $pages = PageModel::findPublishedSubpagesWithoutGuestsByPid($pid, $this->showHidden);

        if ($pages === null) {
            return '';
        }

        $items = array();
        $groups = array();

        // Get all groups of the current front end user
        if (\Contao\System::getContainer()->get('security.helper')->isGranted('ROLE_MEMBER')) {
            $groups = FrontendUser::getInstance()->groups;
        }

        /** @var PageModel $objPage */
        global $objPage;

        // Browse subpages
        foreach ($pages as $subpage) {
            $subitems = '';
            $trail = false;
            $_groups = StringUtil::deserialize($subpage->groups);

            // Do not show protected pages unless a back end or front end user is logged in
            if (!$this->showProtected
                && $subpage->protected
                && !\Contao\System::getContainer()->get('contao.security.token_checker')->isPreviewMode()
                && (!\is_array($_groups) || !\count(array_intersect($_groups, $groups)))
            ) {
                continue;
            }

            // Check whether there will be subpages
            if ($subpage->subpages > 0 && (!$this->showLevel || $this->showLevel >= $level)) {
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
            $row['title'] = StringUtil::specialchars($subpage->title, true);
            $row['pageTitle'] = StringUtil::specialchars($subpage->pageTitle, true);
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

        /** @var FrontendTemplate|object $objTemplate */
        $objTemplate = new FrontendTemplate($this->navigationTpl);

        $objTemplate->pid = $pid;
        $objTemplate->type = \get_class($this);
        $objTemplate->cssID = $this->cssID;
        $objTemplate->level = 'level_'.$level;
        $objTemplate->items = $items;

        return $objTemplate->parse();
    }
}

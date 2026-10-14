<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Menu;

use Codefog\HasteBundle\UrlParser;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\Database;
use Contao\Module;
use Isotope\CoreBundle\Exception\RuntimeException;
use Isotope\CoreBundle\Product\Query\Filter\CategoryFilter;
use Isotope\CoreBundle\Product\Query\Filter\FilterInterface;
use Isotope\CoreBundle\Product\Query\ProductQuery;
use Isotope\CoreBundle\Product\Query\ProductQueryFactory;
use Knp\Menu\Attribute\AsMenuBuilder;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * extends Contao\Module to have access to Module::getPublishedSubpagesByPid().
 */
class CategoryFilterBuilder extends Module
{
    /**
     * @noinspection MagicMethodsValidityInspection
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly Security $security,
        private readonly ProductQueryFactory $productQueryFactory,
        private readonly UrlParser $urlParser,
    ) {
    }

    #[AsMenuBuilder('isotope_categoryfilter')]
    public function __invoke(array $options): ItemInterface
    {
        $this->arrData = $options;

        $menu = $this->factory->createItem('root');
        [$productQuery, $currentIds] = $this->getCurrentIds($options['filterKey']);

        $this->renderTree($menu, (int) $options['rootPage'], 1, $productQuery, $options['filterKey'], $currentIds);

        return $menu;
    }

    protected function compile(): never
    {
        throw new RuntimeException('Invalid call to '.__METHOD__);
    }

    private function renderTree(ItemInterface $menu, int $pid, int $level, ProductQuery|null $productQuery, string $filterKey, array $currentIds): void
    {
        $database = Database::getInstance();
        $pages = static::getPublishedSubpagesByPid($pid, $this->showHidden);

        if (null === $pages) {
            return;
        }

        foreach ($pages as ['page' => $subpage, 'hasSubpages' => $hasSubpages]) {
            $subpage->loadDetails();

            if ($subpage->guests && $this->security->isGranted('ROLE_MEMBER')) {
                continue;
            }

            // Do not show protected pages unless a back end or front end user is logged in
            if (
                !$this->showProtected
                && $subpage->protected
                && !$this->security->isGranted(ContaoCorePermissions::MEMBER_IN_GROUPS, $subpage->groups)
            ) {
                continue;
            }

            $hasSubpages = $hasSubpages && (!$this->showLevel || $this->showLevel > $level);

            $item = $menu
                ->addChild('tl_page.'.$subpage->id)
                ->setLabel($subpage->title)
                ->setCurrent(\in_array((int) $subpage->id, $currentIds, true))
                ->setLinkAttribute('data-isotope--productquery-operator-param', 'cat')
                ->setAttribute('class', trim(($hasSubpages ? 'submenu' : '').' '.($subpage->protected ? 'protected' : '').' '.$subpage->cssClass))
            ;

            $childIds = [(int) $subpage->id];
            $childIds = $database->getChildRecords($childIds, 'tl_page', false, $childIds);

            if ($item->isCurrent()) {
                $value = implode(',', array_diff($currentIds, $childIds, $subpage->trail));
            } else {
                $value = implode(',', array_unique(array_merge($currentIds, $childIds)));
            }

            $filters = array_filter(
                $productQuery?->getFilters([$filterKey]) ?? [],
                static fn (FilterInterface $filter) => !$filter instanceof CategoryFilter,
            );

            if ('' !== $value) {
                $filters[] = new CategoryFilter(null, $value);
            }

            if ([] === $filters) {
                $item->setUri($this->urlParser->removeQueryString(['_f'.$filterKey]));
            } else {
                $item->setUri($this->urlParser->addQueryString(http_build_query([
                    '_f'.$filterKey => implode(';', array_map(strval(...), $filters)),
                ])));
            }

            $item->setUri($this->urlParser->removeQueryStringCallback(
                static fn (mixed $value, string|int $key) => !preg_match('/^_p\d+$/', (string) $key),
                $item->getUri(),
            ));

            if ('' === $value) {
                $item->setLinkAttribute('data-action', 'isotope--productquery#unset:prevent');
            } else {
                $item
                    ->setLinkAttribute('data-action', 'isotope--productquery#set:prevent')
                    ->setLinkAttribute('data-isotope--productquery-value-param', $value)
                ;
            }

            if ($hasSubpages) {
                $this->renderTree($item, (int) $subpage->id, $level + 1, $productQuery, $filterKey, $currentIds);
            }
        }
    }

    private function getCurrentIds(string $filterKey): array
    {
        $productQuery = $this->productQueryFactory->getCurrent();

        if (!$productQuery instanceof ProductQuery) {
            return [null, []];
        }

        foreach ($productQuery->getFilters([$filterKey]) as $filter) {
            if ($filter instanceof CategoryFilter) {
                return [$productQuery, array_map(intval(...), $filter->values)];
            }
        }

        return [$productQuery, []];
    }
}

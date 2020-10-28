<?php

use Bitrix\Main\Loader;
use Paul\Links\LinkGenerator;
use Paul\Links\Orm\LinksStatisticsTable;
use Bitrix\Main\UI\PageNavigation;

Loader::includeModule("paul.links");

class LinkStatistics extends CBitrixComponent
{

    public function executeComponent()
    {
        try {
            $this->includeComponentLang('class.php');

            if ($this->request['SHORT_LINK']) {
                $this->prepareLinkStatistic($this->request['SHORT_LINK']);
            }

            $this->includeComponentTemplate();

        } catch (\Exception $exception) {
            ShowError($exception->getMessage());
        }
    }

    /**
     * Preparing link statistic with pagen
     *
     * @param $shortLink
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private function prepareLinkStatistic($shortLink)
    {
        $nav = new PageNavigation("link_pagen");
        $nav->allowAllRecords(true)->setPageSize($this->arParams['ELEMENTS_ON_PAGE'])->initFromUri();

        $rsLinkStatistic = LinksStatisticsTable::getList([
            'filter' => [
                '=LINK.LINK_SHORT_KEY' => $shortLink
            ],
            'select' => [
                '*',
                'LINK_SHORT_KEY' => 'LINK.LINK_SHORT_KEY'
            ],
            'order' => [
                'TRANSITION_DATE' => 'DESC'
            ],
            'count_total' => true,
            'limit' => $nav->getLimit(),
            'offset' => $nav->getOffset()
        ]);
        
        $nav->setRecordCount($rsLinkStatistic->getCount());

        $numCoefficient = ($nav->getCurrentPage() - 1) * $nav->getPageSize();
        $curNumber = 1;
        while ($arLinkStatistic = $rsLinkStatistic->fetch()) {
            $curNumber += $numCoefficient;
            $arLinkStatistic['INDEX_NUMBER'] = $curNumber++;
            $this->arResult['ITEMS'][$arLinkStatistic['ID']] = $arLinkStatistic;
        }

        $this->arResult['LINK'] = LinkGenerator::createShortURL(current($this->arResult['ITEMS'])['LINK_SHORT_KEY'])['SHORT_URL'];
        $this->arResult['NAV_OBJ'] = $nav;
    }
}
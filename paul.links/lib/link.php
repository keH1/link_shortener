<?php


namespace Paul\Links;


use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Type\DateTime;
use Paul\Links\Orm\LinksStatisticsTable;

class Link
{
    /**
     * Save link transition
     *
     * @param $urlData
     * @return bool
     * @throws ArgumentNullException
     */
    public static function saveTransition($urlData)
    {
        if (!$urlData['ID']) {
            throw new ArgumentNullException('Link ID');
        }

        $geoData = GeoInformer::getGeoDataByIP();

        $arTransitionData = [
            'LINK_ID' => $urlData['ID'],
            'IP' => $geoData['ip'],
            'GEO_DATA' => $geoData,
            'USER_AGENT' => UserAgentParser::parseUserAgent(),
        ];

        $transitionResult = LinksStatisticsTable::add($arTransitionData);

        if ($transitionResult->isSuccess()) {
            return true;
        } else {
            throw new \Exception('Something went wrong, please reply to admin.');
        }
    }

    /**
     * Checking is the link expired
     *
     * @param $linkData
     * @return bool
     * @throws ArgumentNullException
     */
    public static function isExpired($linkData)
    {
        if (!$linkData['ID']) {
            throw new ArgumentNullException('Link ID');
        }

        if (!$linkData['END_DATE']) {
            throw new ArgumentNullException('End date');
        }

        $curDate = new DateTime();

        if ($linkData['END_DATE'] < $curDate) {
            return true;
        } else {
            return false;
        }
    }
}
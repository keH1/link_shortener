<?php


namespace Paul\Links;


use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\DateTime;
use Paul\Links\Orm\LinksTable;

class LinkGenerator
{
    const MIN_LETTERS = 6;

    /**
     * Generating add to DB and return short URL
     * @param $url
     * @param DateTime|null $ltv - life time value
     * @return string[]
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public static function generateShortURL($url, DateTime $ltv = null)
    {
        $uniqCode = self::createUniqLink();

        //Add url to DB
        $arAdd = [
            'ORIGINAL_LINK' => $url,
            'LINK_SHORT_KEY' => $uniqCode,
        ];

        if (!is_null($ltv)) {
            $arAdd['END_DATE'] = $ltv;
        }

        $rsResult = LinksTable::add($arAdd);

        if ($rsResult->isSuccess()) {
            return self::createShortURL($uniqCode);
        } else {
            throw new \Exception(implode(', ', $rsResult->getErrorMessages()));
        }
    }

    /**
     * Creating and checking unique link code
     *
     * @return false|string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function createUniqLink()
    {
        $uniqCode = self::generateUniqLinkCode();

        //Check is exist this uniq code in table
        $rsResult = LinksTable::getList([
            'filter' => [
                '=LINK_SHORT_KEY' => $uniqCode
            ]
        ]);

        if ($rsResult->getSelectedRowsCount() > 0) {
            self::createUniqLink();
        }

        return $uniqCode;
    }

    /**
     * Creating uniq short URL
     *
     * @param $uniqCode
     * @return string[] - return short and statistics URL
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public static function createShortURL($uniqCode)
    {
        $shortDirectory = Option::get('paul.links', 'PUBLIC_DIR');

        return [
            'SHORT_URL' => 'http://' . $_SERVER['SERVER_NAME'] . DIRECTORY_SEPARATOR . $shortDirectory . DIRECTORY_SEPARATOR . $uniqCode,
            'STATISTIC_URl' => 'http://' . $_SERVER['SERVER_NAME'] . DIRECTORY_SEPARATOR . $shortDirectory . DIRECTORY_SEPARATOR . $uniqCode . '/stat'
        ];
    }

    /**
     * Generating short uniq url code
     *
     * @return false|string
     */
    public static function generateUniqLinkCode()
    {
        return randString(self::MIN_LETTERS, array(
            "abcdefghijklnmopqrstuvwxyz",
            "ABCDEFGHIJKLNMOPQRSTUVWXYZ",
            "0123456789",
        ));
    }
}
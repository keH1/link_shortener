<?php


namespace Paul\Links\Orm;


use Bitrix\Main\Entity\StringField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\Type;

class LinksTable extends DataManager
{
    /**
     * Db table name
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'paul_links';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     * @throws \Bitrix\Main\SystemException
     */
    public static function getMap()
    {
        return [
            /**
             * Link ID
             */ new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true
            ]),

            /**
             * Original link
             */
            new StringField('ORIGINAL_LINK', [
                'required' => true,
            ]),

            /**
             * Link short key
             */
            new StringField('LINK_SHORT_KEY', [
                'required' => true,
            ]),

            /**
             * Date create
             */ new DatetimeField('DATE_CREATE', [
                'required' => true,
                'default_value' => new Type\DateTime
            ]),

            /**
             * Date end
             */ new DatetimeField('END_DATE', [
                'default_value' => new Type\DateTime('31.12.2999 23:59:59')
            ]),
        ];
    }
}
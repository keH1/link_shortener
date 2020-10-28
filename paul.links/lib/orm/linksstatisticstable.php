<?php


namespace Paul\Links\Orm;


use Bitrix\Main\Entity\StringField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type;

class LinksStatisticsTable extends DataManager
{
    /**
     * Db table name
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'paul_links_statistic';
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
             * ID
             */ new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true
            ]),

            /**
             * Link ID
             */ new IntegerField('LINK_ID', [
                'required' => true
            ]),
            (new Reference('LINK', LinksTable::class, Join::on('this.LINK_ID', 'ref.ID')))->configureJoinType('inner'),

            /**
             * IP
             */ new StringField('IP', [
                'required' => true,
            ]),

            /**
             * Date of transition
             */ new DatetimeField('TRANSITION_DATE', [
                'required' => true,
                'default_value' => new Type\DateTime
            ]),

            /**
             * Transition geo data
             */ new TextField('GEO_DATA', [
                'save_data_modification' => function () {
                    return array(
                        function ($value) {
                            if (is_array($value)) {
                                return json_encode(array_change_key_case($value, CASE_UPPER), JSON_UNESCAPED_UNICODE);
                            } else {
                                return $value;
                            }

                        }
                    );
                },
                'fetch_data_modification' => function () {
                    return array(
                        function ($value) {
                            $arr = json_decode($value, true);
                            if (is_string($value) && is_array($arr)) {
                                return $arr;
                            } else {
                                return $value;
                            }

                        }
                    );
                }
            ]),

            /**
             * Transition user-agent
             */ new TextField('USER_AGENT', [
                'save_data_modification' => function () {
                    return array(
                        function ($value) {
                            if (is_array($value)) {
                                return json_encode(array_change_key_case($value, CASE_UPPER), JSON_UNESCAPED_UNICODE);
                            } else {
                                return $value;
                            }
                        }
                    );
                },
                'fetch_data_modification' => function () {
                    return array(
                        function ($value) {
                            $arr = json_decode($value, true);
                            if (is_string($value) && is_array($arr)) {
                                return $arr;
                            } else {
                                return $value;
                            }
                        }
                    );
                }
            ]),
        ];
    }
}
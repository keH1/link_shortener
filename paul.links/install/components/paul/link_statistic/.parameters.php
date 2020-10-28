<?

use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */

$arComponentParameters = array(
	"GROUPS" => array(
	),
	"PARAMETERS" => array(
        "LIMIT" => array(
            "NAME" => Loc::getMessage("PAUL_LINKS_STATISTIC_LIMIT"),
            "TYPE" => "TEXT",
            "DEFAULT" => "50"
        ),
	),
);
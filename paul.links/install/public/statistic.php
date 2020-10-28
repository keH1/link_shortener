<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Статистика по ссылке");
$APPLICATION->IncludeComponent(
    "paul:link_statistic",
    ".default",
    [
        'ELEMENTS_ON_PAGE' => 50
    ],
    false
);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
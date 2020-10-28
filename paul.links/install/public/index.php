<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Сокращение ссылок");
$APPLICATION->IncludeComponent(
    "paul:create_link",
    ".default",
    [],
    false
);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>
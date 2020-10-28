<?php

use \Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid())
    return;

if ($EXSEPTION)
    echo CAdminMessage::ShowMessage(array(
        "TYPE" => "ERROR",
        "MESSAGE" => Loc::getMessage("MOD_INST_ERR"),
        "DETAILS" => $EXSEPTION->getMessage(),
        "HTML" => true,
    ));
else
    echo CAdminMessage::ShowNote('Получай свой модуль');

?>
<form action="<? echo $APPLICATION->GetCurPage(); ?>">
    <input type="hidden" name="lang" value="<? echo LANGUAGE_ID ?>">
    <input type="submit" name="" value="<?echo Loc::getMessage("MOD_BACK"); ?>">
</form>
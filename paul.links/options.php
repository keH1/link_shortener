<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Itkn\Asterisk\AMI;
use Itkn\Asterisk\Checker;

Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/options.php");
Loc::loadMessages(__FILE__);

$request = Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();
$module_id = htmlspecialcharsbx($request["mid"] != "" ? $request["mid"] : $request["id"]);

Bitrix\Main\Loader::includeModule($module_id);

/**
 * Описание опций
 */
$aTabs = array(
    array(
        'DIV' => 'preferences',
        'TAB' => Loc::getMessage('PAUL_MODULE_TAB_PREFERENCES'),
        'OPTIONS' => array(
            array(
                'PUBLIC_DIR',
                Loc::getMessage('PAUL_MODULE_PUBLIC_DIR'),
                'sl',
                array('text')
            ),
        )
    )
);

/**
 * Сохранение
 */
if ($request->isPost() && $request['Update'] && check_bitrix_sessid()) {
    foreach ($aTabs as $aTab) {
        foreach ($aTab['OPTIONS'] as $arOption) {
            if (!is_array($arOption)) //Строка с подсветкой. Используется для разделения настроек в одной вкладке
            {
                continue;
            }

            if ($arOption['note']) //Уведомление с подсветкой
            {
                continue;
            }

            $optionName = $arOption[0];

            $oldOptionValue = Option::get($module_id, $optionName);
            $optionValue = $request->getPost($optionName);

            Option::set($module_id, $optionName, is_array($optionValue) ? implode(",", $optionValue) : $optionValue);

            if ($optionName == 'PUBLIC_DIR') {
                rename($_SERVER['DOCUMENT_ROOT'] . '/' . $oldOptionValue,
                    $_SERVER['DOCUMENT_ROOT'] . '/' . $optionValue);

                $moduleInstaller = new paul_links();
                $moduleInstaller->UnInstallRoutes();
                $moduleInstaller->InstallRoutes();
            }
        }
    }
}

/**
 * Визуальный вывод
 */
$tabControl = new CAdminTabControl('tabControl', $aTabs);
?>
<? $tabControl->Begin(); ?>
    <form method='post'
          action='<? echo $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialcharsbx($request['mid']) ?>&amp;
    lang=<?= $request['lang'] ?>' name='PAUL_MODULE_settings'>

        <? foreach ($aTabs as $aTab):
            if ($aTab['OPTIONS']):?>
                <? $tabControl->BeginNextTab(); ?>
                <? __AdmSettingsDrawList($module_id, $aTab['OPTIONS']); ?>
            <? endif;
        endforeach; ?>

        <?
        $tabControl->BeginNextTab();


        $tabControl->Buttons(); ?>

        <input type="submit" name="Update" value="<? echo Loc::getMessage('MAIN_SAVE') ?>" class="adm-btn-save">
        <input type="reset" name="reset" value="<? echo Loc::getMessage('MAIN_RESET') ?>">
        <?= bitrix_sessid_post(); ?>
    </form>
<? $tabControl->End(); ?>
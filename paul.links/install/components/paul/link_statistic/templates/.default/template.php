<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arResult */

use Bitrix\Main\UI\Extension;

Extension::load('ui.bootstrap4');
?>
<? if ($arResult['ITEMS']): ?>
    <div class="content">
        <div class="row">
            <div class="col mb-3">
                <p class="h3 text-center">Link transition statistic for <?= $arResult['LINK'] ?></p>
            </div>
        </div>
        <table class="table table-striped">
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">IP</th>
                <th scope="col">Transition date</th>
                <th scope="col">GEO</th>
                <th scope="col">User info</th>
            </tr>
            </thead>
            <tbody>
            <? foreach ($arResult['ITEMS'] as $arItem): ?>
                <tr>
                    <th class="align-middle" scope="row"><?= $arItem['INDEX_NUMBER'] ?></th>
                    <td class="align-middle"><?= $arItem['IP'] ?></td>
                    <td class="align-middle"><?= $arItem['TRANSITION_DATE'] ?></td>
                    <td class="align-middle">
                        <img src="<?= $arItem['GEO_DATA']['COUNTRY_FLAG'] ?>" class="img-thumbnail" width="40"
                             alt="GEO">
                        <?= $arItem['GEO_DATA']['CITY'] ?>, <?= $arItem['GEO_DATA']['COUNTRY'] ?>
                    </td class="align-middle">
                    <td class="align-middle"><?= implode(', ', $arItem['USER_AGENT']) ?></td>
                </tr>
            <? endforeach; ?>
            </tbody>
        </table>
    </div>
<? endif;; ?>
<?= $APPLICATION->IncludeComponent("bitrix:main.pagenavigation", "", array(
    "NAV_OBJECT" => $arResult["NAV_OBJ"],
    "SEF_MODE" => "N",
), false); ?>
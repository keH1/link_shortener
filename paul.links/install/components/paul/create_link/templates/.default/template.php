<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\UI\Extension;

Extension::load('ui.bootstrap4');
CJSCore::Init(array("jquery", "date"));

?>
<div class="container">
    <div class="row justify-content-center">
        <? if (!$arResult['SHORT_URL']): ?>
            <div class="col-8">
                <form class="form" method="post">
                    <input type="hidden" name="ACTION" value="short_url">
                    <div class="input-group input-group-lg mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon3">URL</span>
                        </div>
                        <input name="URL" type="text" class="form-control" id="basic-url"
                               aria-describedby="basic-addon3" placeholder="Example: http://google.com/">
                    </div>
                    <label class="sr-only" for="inlineFormInputGroupUsername2">Date expired</label>
                    <div class="input-group mb-2 mr-sm-2">
                        <input name="DATE_EXPIRED" type="text" class="form-control" id="inlineFormInputGroupUsername2"
                               placeholder="Date expired"
                               onclick="BX.calendar({node: this, field: this, bTime: true});">
                    </div>
                    <div class="row justify-content-center mt-4">
                        <button type="submit" class="btn btn-primary mb-2">Short your URL</button>
                    </div>
                </form>
            </div>
        <?else:?>
            <div class="col-8">
                <div class="alert alert-success" role="alert" style="text-align: center">
                    <p class="h1">URL <br><a target="_blank" href="<?=$arResult['SHORT_URL']['SHORT_URL']?>"><?=$arResult['SHORT_URL']['SHORT_URL']?></a></p>
                    <br>
                    <p class="h4">URL with statistic <br><a target="_blank" href="<?=$arResult['SHORT_URL']['STATISTIC_URl']?>"><?=$arResult['SHORT_URL']['STATISTIC_URl']?></a></p>
                </div>
            </div>
        <? endif; ?>
    </div>
</div>

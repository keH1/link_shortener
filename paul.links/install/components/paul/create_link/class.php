<?php

use Bitrix\Main\Loader;
use Paul\Links\LinkGenerator;
use Paul\Links\Link;
use Paul\Links\Orm\LinksTable;

Loader::includeModule("paul.links");

class CreateLink extends CBitrixComponent
{

    public function executeComponent()
    {
        try {
            $this->includeComponentLang('class.php');

            if ($this->request['SHORT_LINK']) {
                $this->tryToRedirect($this->request['SHORT_LINK']);
            } elseif ($this->request['ACTION'] == 'short_url') {
                $this->arResult['REQUEST_DATA'] = [
                    'URL' => $this->request['URL'],
                    'DATE_EXPIRED' => $this->request['DATE_EXPIRED']
                ];

                if (!filter_var($this->arResult['REQUEST_DATA']['URL'], FILTER_VALIDATE_URL)) {
                    $this->arResult['ERRORS']['URL'] = 'Enter correct URL';
                }

                if (!$this->arResult['ERRORS']) {
                    $expDate = $this->arResult['REQUEST_DATA']['DATE_EXPIRED'] ? new \Bitrix\Main\Type\DateTime($this->arResult['REQUEST_DATA']['DATE_EXPIRED']) : null;

                    $this->arResult['SHORT_URL'] = LinkGenerator::generateShortURL($this->arResult['REQUEST_DATA']['URL'],
                        $expDate);
                }
            }

            $this->includeComponentTemplate();

        } catch (\Exception $exception) {
            ShowError($exception->getMessage());
        }
    }

    /**
     * Short URL redirection
     *
     * @param $shortLink
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws Exception
     */
    private function tryToRedirect($shortLink)
    {
        $rsLink = LinksTable::getList([
            'filter' => [
                '=LINK_SHORT_KEY' => $shortLink
            ]
        ]);

        if ($rsLink->getSelectedRowsCount() > 0) {
            $urlData = $rsLink->fetch();

            if (!Link::isExpired($urlData)) {
                if (Link::saveTransition($urlData)) {
                    LocalRedirect($urlData['ORIGINAL_LINK'], true);
                }
            } else {
                throw new \Exception('Link is expired!');
            }

        } else {
            throw new \Exception('URL was not found.');
        }
    }
}
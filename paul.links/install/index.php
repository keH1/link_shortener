<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use \Bitrix\Main\Application;
use Bitrix\Main\SystemException;
use Bitrix\Main\Entity\Base;

Loc::loadMessages(__FILE__);

class paul_links extends CModule
{
    public function __construct()
    {
        if (file_exists(__DIR__ . "/version.php")) {
            $arModuleVersion = array();
            include_once(__DIR__ . "/version.php");

            $this->MODULE_ID = str_replace("_", ".", get_class($this));
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
            $this->MODULE_NAME = Loc::getMessage("PAUL_MODULE_NAME");
            $this->MODULE_DESCRIPTION = Loc::getMessage("PAUL_MODULE_DESCRIPTION");

            $this->PARTNER_NAME = Loc::getMessage("PAUL_MODULE_PARTNER_NAME");
            $this->PARTNER_URI = Loc::getMessage("PAUL_MODULE_PARTNER_URI");
        }

        return false;
    }

    private function GetPath($notDocumentRoot = false)
    {
        if ($notDocumentRoot) {
            return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
        } else {
            return dirname(__DIR__);
        }
    }

    private function isVersionD7()
    {
        if (CheckVersion(ModuleManager::getVersion('main'), '14.00.00')) {
            return true;
        } else {
            throw new SystemException(Loc::getMessage("PAUL_MODULE_INSTALL_ERROR_VERSION"));
        }
    }

    private function isCurlExists()
    {
        if (function_exists('curl_init')) return true;
        else
            throw new SystemException(Loc::getMessage("PAUL_MODULE_CURL_ERROR"));
    }

    public function DoInstall()
    {
        global $APPLICATION;
        try {
            $this->isVersionD7();
            $this->isCurlExists();

            ModuleManager::registerModule($this->MODULE_ID);

            $this->InstallDB();
            $this->SetDefaultOptions();
            $this->InstallFiles();
            $this->InstallRoutes();

        } catch (Bitrix\Main\SystemException $exception) {
            global $EXSEPTION;
            $EXSEPTION = $exception;
        }
        finally {
            $APPLICATION->IncludeAdminFile(Loc::getMessage("PAUL_MODULE_INSTALL_TITLE"),
                $this->GetPath() . "/install/step.php");
        }
    }

    public function InstallDB()
    {
        Loader::includeModule($this->MODULE_ID);

        if (!Application::getConnection(\Paul\Links\Orm\LinksTable::getConnectionName())->isTableExists(Base::getInstance('\Paul\Links\Orm\LinksTable')->getDBTableName())) {
            Base::getInstance('\Paul\Links\Orm\LinksTable')->createDbTable();
        }

        if (!Application::getConnection(\Paul\Links\Orm\LinksStatisticsTable::getConnectionName())->isTableExists(Base::getInstance('\Paul\Links\Orm\LinksStatisticsTable')->getDBTableName())) {
            Base::getInstance('\Paul\Links\Orm\LinksStatisticsTable')->createDbTable();
        }

        return true;
    }

    public function InstallFiles()
    {
        $pathToComponents = $this->GetPath() . "/install/components";
        $pathToPublic = $this->GetPath() . "/install/public";

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($pathToComponents)) {

            //Copy components
            CopyDirFiles($pathToComponents, $_SERVER['DOCUMENT_ROOT'] . '/local/components', true, true);

        } else {
            throw new Bitrix\Main\IO\InvalidPathException($pathToComponents);
        }

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($pathToPublic)) {

            //Copy public
            CopyDirFiles($pathToPublic,
                $_SERVER['DOCUMENT_ROOT'] . '/' . Option::get($this->MODULE_ID, 'PUBLIC_DIR'), true, true);

        } else {
            throw new Bitrix\Main\IO\InvalidPathException($pathToPublic);
        }

        return true;
    }

    public function InstallAgents()
    {
        return true;
    }

    public function SetDefaultOptions()
    {
        foreach (Option::getDefaults($this->MODULE_ID) as $optionKey => $optionVal) {
            Option::set($this->MODULE_ID, $optionKey, $optionVal);
        }
    }

    public function InstallRoutes()
    {
        \Bitrix\Main\UrlRewriter::add(SITE_ID, [
            'CONDITION' => '#^/'.Option::get($this->MODULE_ID, 'PUBLIC_DIR').'/(.*)$#',
            'RULE' => 'SHORT_LINK=$1',
            'ID' => 'paul.links',
            'PATH' => '/'.Option::get($this->MODULE_ID, 'PUBLIC_DIR').'/index.php',
            'SORT' => 100,
        ]);

        \Bitrix\Main\UrlRewriter::add(SITE_ID, [
            'CONDITION' => '#^/'.Option::get($this->MODULE_ID, 'PUBLIC_DIR').'/(.*)/stat(.*)$#',
            'RULE' => 'SHORT_LINK=$1',
            'ID' => 'paul.links',
            'PATH' => '/'.Option::get($this->MODULE_ID, 'PUBLIC_DIR').'/statistic.php',
            'SORT' => 100,
        ]);
    }

    public function DoUninstall()
    {
        global $APPLICATION;

        $this->UnInstallDB();
        $this->UnInstallFiles();
        $this->UnInstallRoutes();

        ModuleManager::unRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(Loc::getMessage("PAUL_MODULE_INSTALL_TITLE"),
            $this->GetPath() . "/install/unstep.php");
    }

    public function UnInstallDB()
    {
        Loader::includeModule($this->MODULE_ID);

        //Links table
        Application::getConnection(\Paul\Links\Orm\LinksTable::getConnectionName())->queryExecute('drop table if exists ' . Base::getInstance('\Paul\Links\Orm\LinksTable')->getDBTableName());

        //Links statistics table
        Application::getConnection(\Paul\Links\Orm\LinksStatisticsTable::getConnectionName())->queryExecute('drop table if exists ' . Base::getInstance('\Paul\Links\Orm\LinksStatisticsTable')->getDBTableName());

        return true;
    }

    public function UnInstallFiles()
    {
        $pathToComponents = $this->GetPath() . "/install/components";
        $pathToPublic = $_SERVER['DOCUMENT_ROOT'] . '/' . Option::get($this->MODULE_ID, 'PUBLIC_DIR');

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($pathToComponents)) {
            if ($dir = opendir($pathToComponents)) {
                while (false !== $item = readdir($dir)) {
                    if ($item == '..' || $item == '.' || !is_dir($p0 = $pathToComponents . '/' . $item)) {
                        continue;
                    }

                    $dir0 = opendir($p0);
                    while (false !== $item0 = readdir($dir0)) {
                        if ($item0 == '..' || $item0 == '.') {
                            continue;
                        }
                        DeleteDirFilesEx('/local/components/' . $item . '/' . $item0);
                    }
                    closedir($dir0);
                }
                closedir($dir);
            }
        } else {
            throw new Bitrix\Main\IO\InvalidPathException($pathToComponents);
        }

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($pathToPublic)) {
            \Bitrix\Main\IO\Directory::deleteDirectory($pathToPublic);
        } else {
            throw new Bitrix\Main\IO\InvalidPathException($pathToComponents);
        }

        return true;
    }

    public function UnInstallAgents()
    {
        return true;
    }

    public function UnInstallRoutes()
    {
        \Bitrix\Main\UrlRewriter::delete(SITE_ID, [
            'ID' => 'paul.links'
        ]);
    }
}
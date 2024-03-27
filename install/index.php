<?php

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\EventManager;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\IO\Directory;

Loc::loadMessages(__FILE__);

class digitmind_redirecturlwriter extends CModule
{
    var $exclusionAdminFiles;

    function __construct()
    {
        $this->MODULE_ID = 'digitmind.redirecturlwriter';
        $this->MODULE_NAME = Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_MODULE_DESCRIPTION');

        $this->PARTNER_NAME = '';
        $this->PARTNER_URI = '';

        $arModuleVersion = [];
        include(__DIR__ . '/version.php');
        if (is_array($arModuleVersion)) {
            if (array_key_exists('VERSION', $arModuleVersion)) {
                $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            }

            if (array_key_exists('VERSION_DATE', $arModuleVersion)) {
                $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
            }
        }

        $this->exclusionAdminFiles = [
            '..',
            '.'
        ];
    }

    function DoInstall()
    {
        global $APPLICATION;
        global $errors;

        $errors = '';

        if (!ModuleManager::isModuleInstalled('iblock')) {
            $errors = Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_MODULE_NOT_INSTALLED_IBLOCK');
        } else {
            $documentRoot = Application::getDocumentRoot();
            $this->copyFiles($documentRoot);
            $this->createDirectories($documentRoot);

            $this->RegisterEvents();
            $this->InstallDB();

            ModuleManager::registerModule($this->MODULE_ID);
        }

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_MODULE_INSTALL'),
            __DIR__ . '/step.php'
        );
    }

    function DoUninstall()
    {
        global $APPLICATION;
        global $errors;

        $errors = '';

        $this->deleteFiles();
        $this->deleteDirectories();

        $this->UnRegisterEvents();
        $this->UnInstallDB();

        ModuleManager::unRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_MODULE_UNINSTALL'),
            __DIR__ . '/unstep.php'
        );
    }

    //Определяем место размещения модуля
    public function GetPath($notDocumentRoot = false)
    {
        if ($notDocumentRoot) {
            return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
        } else {
            return dirname(__DIR__);
        }
    }

    //Проверяем что система поддерживает D7
    public function isVersionD7()
    {
        return CheckVersion(ModuleManager::getVersion('main'), '14.00.00');
    }

    function InstallDB()
    {
        global $APPLICATION;
        global $DB;
        global $errors;

        $documentRoot = Application::getDocumentRoot();
        $errors = $DB->RunSQLBatch(
            "{$documentRoot}/bitrix/modules/digitmind.redirecturlwriter/install/db/" . strtolower($DB->type) . '/install.sql'
        );
        if (!empty($errors)) {
            $APPLICATION->ThrowException(implode('. ', $errors));
            return false;
        }

        return true;
    }

    function UnInstallDB()
    {
        global $APPLICATION;
        global $DB;
        global $errors;

        $documentRoot = Application::getDocumentRoot();
        $errors = $DB->RunSQLBatch(
            "{$documentRoot}/bitrix/modules/digitmind.redirecturlwriter/install/db/" . strtolower($DB->type) . '/uninstall.sql'
        );
        if (!empty($errors)) {
            $APPLICATION->ThrowException(implode('. ', $errors));
            return false;
        }

        return true;
    }

    private function copyFiles($documentRoot)
    {
        CopyDirFiles(
            __DIR__ . '/pages/admin/digitmind_redirecturlwriter_xlsxparse.php',
            "{$documentRoot}/bitrix/admin/digitmind_redirecturlwriter_xlsxparse.php",
            true,
            true,
            false
        );

        CopyDirFiles(__DIR__ . '/js', "{$documentRoot}/bitrix/js/{$this->MODULE_ID}", true, true, false);
        CopyDirFiles(__DIR__ . '/css', "{$documentRoot}/bitrix/css/{$this->MODULE_ID}", true, true, false);
        CopyDirFiles(__DIR__ . '/images', "{$documentRoot}/bitrix/images/{$this->MODULE_ID}", true, true, false);
    }

    private function deleteFiles()
    {
        DeleteDirFilesEx('/bitrix/admin/digitmind_redirecturlwriter_xlsxparse.php');

        DeleteDirFilesEx("/bitrix/js/{$this->MODULE_ID}");
        DeleteDirFilesEx("/bitrix/css/{$this->MODULE_ID}");
        DeleteDirFilesEx("/bitrix/images/{$this->MODULE_ID}");
    }

    private function createDirectories($documentRoot)
    {
        $uploadDirectoryName = Option::get('main', 'upload_dir');

        $digitmindDirectoryPath = "{$documentRoot}/{$uploadDirectoryName}/digitmind";
        if (!Directory::isDirectoryExists($digitmindDirectoryPath)) {
            Directory::createDirectory($digitmindDirectoryPath);
        }

        $redirecturlwriterDirectoryPath = "{$digitmindDirectoryPath}/redirecturlwriter";
        if (!Directory::isDirectoryExists($redirecturlwriterDirectoryPath)) {
            Directory::createDirectory($redirecturlwriterDirectoryPath);
        }
    }

    private function deleteDirectories()
    {
        $uploadDirectoryPath = Option::get('main', 'upload_dir');
        DeleteDirFilesEx("/{$uploadDirectoryPath}/digitmind/redirecturlwriter");
    }

    function RegisterEvents()
    {
        EventManager::getInstance()->registerEventHandler(
            'main',
            'OnEpilog',
            $this->MODULE_ID,
            'DigitMind\RedirectUrlWriter\Events\MainEvents',
            'EpilogHandler',
            1000
        );
    }

    function UnRegisterEvents()
    {
        EventManager::getInstance()->unRegisterEventHandler(
            'main',
            'OnEpilog',
            $this->MODULE_ID,
            'DigitMind\RedirectUrlWriter\Events\MainEvents',
            'EpilogHandler'
        );
    }

    function GetModuleRightList()
    {
        return [
            "reference_id" => ['D'],
            "reference" => [
                '[D] ' . Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_RIGHT_DENIED')
            ]
        ];
    }
}

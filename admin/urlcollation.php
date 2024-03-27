<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Application;
use Bitrix\Main\IO\File;
use DigitMind\RedirectUrlWriter\Helpers\MiscHelper;
use DigitMind\RedirectUrlWriter\Entities\OptionsTable;
use Shuchkin\SimpleXLSX;

define('OPT_NAME_CSV_FILE_PATH', 'CSV_FILE_PATH');

Loc::loadMessages(__FILE__);
Loader::includeModule('digitmind.redirecturlwriter');

@set_time_limit(360);

global $APPLICATION;
$APPLICATION->SetTitle(Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_URLCOLLATION_PAGE_TITLE'));

$mainCss = MiscHelper::getAssetsPath('css') . '/main.css';
Asset::getInstance()->addString('<link href="' . $mainCss . '" rel="stylesheet" type="text/css">');

Asset::getInstance()->addJs(MiscHelper::getAssetsPath('js') . '/main.js');
Asset::getInstance()->addJs(MiscHelper::getAssetsPath('js') . '/urlcollation.js');

$request = Application::getInstance()->getContext()->getRequest();

CAdminFileDialog::ShowScript(
    [
        'event' => 'OpenFileDialog',
        'arResultDest' => ['ELEMENT_ID' => 'selected_file_path'],
        'arPath' => [],
        'select' => 'F',
        'operation' => 'O',
        'showUploadTab' => true,
        'showAddToMenuTab' => false,
        'fileFilter' => 'xlsx',
        'allowAllFiles' => false,
        'saveConfig' => true
    ]
);

if ($request->isPost()) {
    //
}

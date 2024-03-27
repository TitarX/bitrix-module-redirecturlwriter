<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Application;
use Bitrix\Main\IO\File;
use DigitMind\RedirectUrlWriter\Helpers\MiscHelper;
use DigitMind\RedirectUrlWriter\Entities\OptionsTable;
use Shuchkin\SimpleXLSX;

define('OPT_NAME_XLSX_FILE_PATH', 'XLSX_FILE_PATH');

Loc::loadMessages(__FILE__);
Loader::includeModule('digitmind.redirecturlwriter');

@set_time_limit(360);

global $APPLICATION;
$APPLICATION->SetTitle(Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_XLSXPARSE_PAGE_TITLE'));

$mainCss = MiscHelper::getAssetsPath('css') . '/main.css';
Asset::getInstance()->addString('<link href="' . $mainCss . '" rel="stylesheet" type="text/css">');

Asset::getInstance()->addJs(MiscHelper::getAssetsPath('js') . '/main.js');
Asset::getInstance()->addJs(MiscHelper::getAssetsPath('js') . '/xlsxparse.js');

$request = Application::getInstance()->getContext()->getRequest();

$rsParamsCount = OptionsTable::getCount();
if (empty($rsParamsCount) || !is_int($rsParamsCount)) {
    $rsParamsCount = 0;
}

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
    if ($request->get('action') === 'checkfileexists') { // Проверка на существование выбранного файла
        $APPLICATION->RestartBuffer();

        $result = ['result' => 'miss'];
        $phpInput = file_get_contents('php://input');
        $phpInput = json_decode($phpInput, true);
        if (!empty($phpInput['filepath'])) {
            $documentRoot = Application::getDocumentRoot();
            $fullFilePath = $documentRoot . $phpInput['filepath'];
            $file = new File($fullFilePath);
            if ($file->isExists() && $file->isFile()) {
                $result['result'] = 'yes';
            } else {
                $result['result'] = 'no';
            }
        }

        print json_encode($result);

        exit();
    } elseif ($request->get('action') === 'saveparams') { // Сохранение параметров обновления
        $APPLICATION->RestartBuffer();

        $phpInput = file_get_contents('php://input');
        $phpInput = json_decode($phpInput, true);

        $entryId = 0;
        if ($rsParamsCount !== 1) {
            OptionsTable::getEntity()->getConnection()->queryExecute(
                'TRUNCATE TABLE digitmind_redirecturlwriter_options'
            );
        } elseif (!empty($phpInput['entryid']) && is_numeric($phpInput['entryid'])) {
            $entryId = $phpInput['entryid'];
        }

        $arrParams = [
            'CODE' => OPT_NAME_XLSX_FILE_PATH,
            'VALUE' => $phpInput
        ];

        try {
            $workResult = null;
            if (!empty($entryId)) {
                $workResult = OptionsTable::update($entryId, $arrParams);
            } else {
                $workResult = OptionsTable::add($arrParams);
            }
            $result = [];
            if (isset($workResult) && $workResult->isSuccess()) {
                $entryId = $workResult->getId();
                $result['result'] = $entryId;
            } else {
                $result['result'] = 'fail';
            }
        } catch (Exception $ex) {
            file_put_contents(__DIR__ . '/exceptions.txt', print_r($ex, true));
        }

        print json_encode($result);

        exit();
    } elseif ($request->get('action') === 'parsexlsx') { // Парсинг XLSX
        $APPLICATION->RestartBuffer();

        $result = [];

        $phpInput = file_get_contents('php://input');
        $phpInput = json_decode($phpInput, true);

        $documentRoot = Application::getDocumentRoot();
        $fullFilePath = $documentRoot . $phpInput['filepath'];
        $file = new File($fullFilePath);
        if ($file->isExists() && $file->isFile()) {
            if ($xlsx = SimpleXLSX::parse($fullFilePath)) {
                //
            } else {
                // $parseError =  SimpleXLSX::parseError();
                // file_put_contents(__DIR__ . '/error.txt', print_r($parseError, true));

                $result['result'] = 'xlsxparseerror';
            }
        } else {
            $result['result'] = 'filenotfound';
        }

        print json_encode($result);

        exit();
    } elseif ($request->getPost('action') === 'message') { // Системное сообщение
        $APPLICATION->RestartBuffer();

        $messageType = $request->getPost('type');
        $messageText = $request->getPost('text');
        $messageArgs = $request->getPost('args');
        if (!is_array($messageArgs)) {
            $messageArgs = [];
        }

        $message = vsprintf(Loc::getMessage($messageText), $messageArgs);
        \CAdminMessage::ShowMessage(['MESSAGE' => $message, 'TYPE' => $messageType]);

        exit();
    }
}

$entryId = '';
$filePath = '';
if (!empty($rsParamsCount)) {
    $dbResult = OptionsTable::getList(
        [
            'select' => ['ID', 'VALUE'],
            'order' => ['ID' => 'desc'],
            'filter' => ['CODE' => OPT_NAME_XLSX_FILE_PATH]
        ]
    );
    if ($arrResult = $dbResult->fetch()) {
        $entryId = $arrResult['ID'];

        $arrParams = $arrResult['VALUE'];
        if (!empty($arrParams)) {
            if (!empty($arrParams['filepath'])) {
                $filePath = $arrParams['filepath'];
            }
        }
    }
}
?>

<div class="wrapper">
    <div id="work-info"></div>
</div>

<div class="wrapper">
    <?= Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_XLSXPARSE_PAGE_DESCRIPTION') ?>
</div>

<div class="wrapper">
    <input type="text" name="selected_file_path" id="selected_file_path" value="<?= $filePath ?>" size="64"
           placeholder="<?= Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_XLSXPARSE_FILEPATH_PLACEHOLDER_TITLE') ?>"
           readonly
           required>
    <button id='open_file_dialog_button'>Открыть</button>
</div>

<input type="hidden" name="requested-page" id="requested-page" value="<?= $request->getRequestedPage() ?>">
<input type="hidden" name="params-entry-id" id="params-entry-id" value="<?= $entryId ?>">

<div class="wrapper">
    <button id="start-work-button">
        <?= Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_XLSXPARSE_FILE_START_BUTTON') ?>
    </button>
</div>

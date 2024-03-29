<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Application;
use Bitrix\Main\IO\File;
use DigitMind\RedirectUrlWriter\Helpers\MiscHelper;
use DigitMind\RedirectUrlWriter\Entities\OptionsTable;
use DigitMind\RedirectUrlWriter\Workers\Parser;

define('OPT_NAME_XLSX_FILE_PATH', 'XLSX_FILE_PATH');
define('OPT_NAME_FREE_PRODUCTS_URLS_FILE_PATH', 'FREE_PRODUCTS_URLS_FILE_PATH');
define('OPT_NAME_FREE_SECTIONS_URLS_FILE_PATH', 'FREE_SECTIONS_URLS_FILE_PATH');

Loc::loadMessages(__FILE__);
Loader::includeModule('digitmind.redirecturlwriter');

@set_time_limit(360);

global $APPLICATION;
$APPLICATION->SetTitle(Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_XLSXPARSE_PAGE_TITLE'));

$mainCss = MiscHelper::getAssetsPath('css') . '/main.css';
Asset::getInstance()->addString('<link href="' . $mainCss . '" rel="stylesheet" type="text/css">');

CJSCore::Init(['jquery']);

Asset::getInstance()->addJs(MiscHelper::getAssetsPath('js') . '/main.js');
Asset::getInstance()->addJs(MiscHelper::getAssetsPath('js') . '/xlsxparse.js');

$request = Application::getInstance()->getContext()->getRequest();
$options = OptionsTable::getData();

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
    $action = $request->get('action');
    $action = trim($action);

    if ($action === 'checkfileexists') { // Проверка на существование выбранного файла
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
    } elseif ($action === 'saveparams') { // Сохранение параметров обновления
        $APPLICATION->RestartBuffer();

        $phpInput = file_get_contents('php://input');
        $phpInput = json_decode($phpInput, true);

        $result['result'] = 'fail';
        if (!empty($phpInput['filepath'])) {
            $arrParams = [
                'CODE' => OPT_NAME_XLSX_FILE_PATH,
                'VALUE' => $phpInput['filepath']
            ];

            $workResult = null;
            if (!empty($options[OPT_NAME_XLSX_FILE_PATH]['ID'])) {
                $workResult = OptionsTable::update($options[OPT_NAME_XLSX_FILE_PATH]['ID'], $arrParams);
            } else {
                $workResult = OptionsTable::add($arrParams);
            }
            $result = [];
            if (isset($workResult) && $workResult->isSuccess()) {
                $result['result'] = $workResult->getId();
            }
        }

        print json_encode($result);

        exit();
    } elseif ($action === 'parsexlsx') { // Парсинг XLSX
        $APPLICATION->RestartBuffer();

        $result = [];

        $phpInput = file_get_contents('php://input');
        $phpInput = json_decode($phpInput, true);

        $documentRoot = Application::getDocumentRoot();
        $fullFilePath = $documentRoot . $phpInput['filepath'];
        $file = new File($fullFilePath);
        if ($file->isExists() && $file->isFile()) {
            $result['result'] = Parser::parseXlsxAndWriteUrls($fullFilePath);

            if (!empty($result['result']['free_products_urls_file_path'])) {
                $arrParams = [
                    'CODE' => OPT_NAME_FREE_PRODUCTS_URLS_FILE_PATH,
                    'VALUE' => $result['result']['free_products_urls_file_path']
                ];
                if (!empty($options[OPT_NAME_FREE_PRODUCTS_URLS_FILE_PATH]['ID'])) {
                    OptionsTable::update($options[OPT_NAME_FREE_PRODUCTS_URLS_FILE_PATH]['ID'], $arrParams);
                } else {
                    OptionsTable::add($arrParams);
                }
            }

            if (!empty($result['result']['free_sections_urls_file_path'])) {
                $arrParams = [
                    'CODE' => OPT_NAME_FREE_SECTIONS_URLS_FILE_PATH,
                    'VALUE' => $result['result']['free_sections_urls_file_path']
                ];
                if (!empty($options[OPT_NAME_FREE_SECTIONS_URLS_FILE_PATH]['ID'])) {
                    OptionsTable::update($options[OPT_NAME_FREE_SECTIONS_URLS_FILE_PATH]['ID'], $arrParams);
                } else {
                    OptionsTable::add($arrParams);
                }
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

$filePath = '';
$options = OptionsTable::getData();
if (!empty($options[OPT_NAME_XLSX_FILE_PATH]['VALUE'])) {
    $filePath = $options[OPT_NAME_XLSX_FILE_PATH]['VALUE'];
}
?>

<script>
    BX.message({
        'DIGITMIND_REDIRECTURLWRITER_XLSXPARSE_PROD_URLS_FILE':
            '<?= GetMessageJS('DIGITMIND_REDIRECTURLWRITER_XLSXPARSE_PROD_URLS_FILE') ?>',
        'DIGITMIND_REDIRECTURLWRITER_XLSXPARSE_SECT_URLS_FILE':
            '<?= GetMessageJS('DIGITMIND_REDIRECTURLWRITER_XLSXPARSE_SECT_URLS_FILE') ?>'
    });
</script>

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

<div class="wrapper">
    <div id="work-info-spinner"></div>
    <button id="start-work-button">
        <?= Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_XLSXPARSE_FILE_START_BUTTON') ?>
    </button>
</div>

<div class="wrapper">
    <div id="work-info"></div>
    <div id="free-product-link">
        <?php if (!empty($options[OPT_NAME_FREE_PRODUCTS_URLS_FILE_PATH]['VALUE'])): ?>
            <a href="<?= $options[OPT_NAME_FREE_PRODUCTS_URLS_FILE_PATH]['VALUE'] ?>" download>
                <?= Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_XLSXPARSE_PROD_URLS_FILE') ?>
            </a>
        <?php endif; ?>
    </div>
    <div id="free-section-link">
        <?php if (!empty($options[OPT_NAME_FREE_SECTIONS_URLS_FILE_PATH]['VALUE'])): ?>
            <a href="<?= $options[OPT_NAME_FREE_SECTIONS_URLS_FILE_PATH]['VALUE'] ?>" download>
                <?= Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_XLSXPARSE_SECT_URLS_FILE') ?>
            </a>
        <?php endif; ?>
    </div>
</div>

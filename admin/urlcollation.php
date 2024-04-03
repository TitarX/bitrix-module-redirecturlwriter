<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\Path;
use DigitMind\RedirectUrlWriter\Helpers\MiscHelper;
use DigitMind\RedirectUrlWriter\Entities\OptionsTable;
use DigitMind\RedirectUrlWriter\Workers\Collator;

define('OPT_NAME_CSV_FILE_PATH', 'CSV_FILE_PATH');
define('OPT_NAME_PRODUCTS_WITHOUT_OLD_URLS_FILE_PATH', 'PRODUCTS_WITHOUT_OLD_URLS_FILE_PATH');
define('OPT_NAME_SECTIONS_WITHOUT_OLD_URLS_FILE_PATH', 'SECTIONS_WITHOUT_OLD_URLS_FILE_PATH');
define('OPT_NAME_BAD_URLS_FILE_PATH', 'BAD_URLS_FILE_PATH');

Loc::loadMessages(__FILE__);
Loader::includeModule('digitmind.redirecturlwriter');

@set_time_limit(360);

global $APPLICATION;
$APPLICATION->SetTitle(Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_URLCOLLATION_PAGE_TITLE'));

$mainCss = MiscHelper::getAssetsPath('css') . '/main.css';
Asset::getInstance()->addString('<link href="' . $mainCss . '" rel="stylesheet" type="text/css">');

CJSCore::Init(['jquery']);

Asset::getInstance()->addJs(MiscHelper::getAssetsPath('js') . '/main.js');
Asset::getInstance()->addJs(MiscHelper::getAssetsPath('js') . '/urlcollation.js');

$request = Application::getInstance()->getContext()->getRequest();
$options = OptionsTable::getData();

CAdminFileDialog::ShowScript(
    [
        'event' => 'OpenFileDialog',
        'arResultDest' => ['FUNCTION_NAME' => 'selectedFilePath'],
        'arPath' => [],
        'select' => 'D',
        'operation' => 'O',
        'showUploadTab' => false,
        'showAddToMenuTab' => false,
        'allowAllFiles' => false,
        'saveConfig' => true
    ]
);

if ($request->isPost()) {
    $action = $request->get('action');
    $action = trim($action);

    if ($action === 'checkfileexists') {
        $APPLICATION->RestartBuffer();

        $result = ['result' => 'miss'];
        $phpInput = file_get_contents('php://input');
        $phpInput = json_decode($phpInput, true);
        if (!empty($phpInput['filepath'])) {
            $documentRoot = Application::getDocumentRoot();
            $dirPath = Path::getDirectory($phpInput['filepath']);
            $fullDirPath = $documentRoot . $dirPath;
            $dir = new Directory($fullDirPath);
            if ($dir->isExists()) {
                $result['result'] = 'yes';
            } else {
                $result['result'] = 'no';
            }
        }

        print json_encode($result);

        exit();
    } elseif ($action === 'saveparams') {
        $APPLICATION->RestartBuffer();

        $phpInput = file_get_contents('php://input');
        $phpInput = json_decode($phpInput, true);

        $result['result'] = 'fail';
        if (!empty($phpInput['filepath'])) {
            $arrParams = [
                'CODE' => OPT_NAME_CSV_FILE_PATH,
                'VALUE' => $phpInput['filepath']
            ];

            $workResult = null;
            if (!empty($options[OPT_NAME_CSV_FILE_PATH]['ID'])) {
                $workResult = OptionsTable::update($options[OPT_NAME_CSV_FILE_PATH]['ID'], $arrParams);
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
    } elseif ($action === 'collateurl') {
        $APPLICATION->RestartBuffer();

        $phpInput = file_get_contents('php://input');
        $phpInput = json_decode($phpInput, true);

        $result['result'] = 'fail';
        if (!empty($phpInput['filepath'])) {
            $result['result'] = Collator::collateUrls($phpInput['filepath']);

            if (!empty($result['result']['products_without_old_urls_file_path'])) {
                $arrParams = [
                    'CODE' => OPT_NAME_PRODUCTS_WITHOUT_OLD_URLS_FILE_PATH,
                    'VALUE' => $result['result']['products_without_old_urls_file_path']
                ];
                if (!empty($options[OPT_NAME_PRODUCTS_WITHOUT_OLD_URLS_FILE_PATH]['ID'])) {
                    OptionsTable::update($options[OPT_NAME_PRODUCTS_WITHOUT_OLD_URLS_FILE_PATH]['ID'], $arrParams);
                } else {
                    OptionsTable::add($arrParams);
                }
            }

            if (!empty($result['result']['sections_without_old_urls_file_path'])) {
                $arrParams = [
                    'CODE' => OPT_NAME_SECTIONS_WITHOUT_OLD_URLS_FILE_PATH,
                    'VALUE' => $result['result']['sections_without_old_urls_file_path']
                ];
                if (!empty($options[OPT_NAME_SECTIONS_WITHOUT_OLD_URLS_FILE_PATH]['ID'])) {
                    OptionsTable::update($options[OPT_NAME_SECTIONS_WITHOUT_OLD_URLS_FILE_PATH]['ID'], $arrParams);
                } else {
                    OptionsTable::add($arrParams);
                }
            }

            if (!empty($result['result']['bad_urls_file_path'])) {
                $arrParams = [
                    'CODE' => OPT_NAME_BAD_URLS_FILE_PATH,
                    'VALUE' => $result['result']['bad_urls_file_path']
                ];
                if (!empty($options[OPT_NAME_BAD_URLS_FILE_PATH]['ID'])) {
                    OptionsTable::update($options[OPT_NAME_BAD_URLS_FILE_PATH]['ID'], $arrParams);
                } else {
                    OptionsTable::add($arrParams);
                }
            }
        }

        print json_encode($result);

        exit();
    } elseif ($request->getPost('action') === 'message') {
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

$folderPath = '';
$options = OptionsTable::getData();
if (!empty($options[OPT_NAME_CSV_FILE_PATH]['VALUE'])) {
    $folderPath = $options[OPT_NAME_CSV_FILE_PATH]['VALUE'];
}
?>

<script>
    BX.message({
        'DIGITMIND_REDIRECTURLWRITER_URLCOLLATION_PROD_URLS_FILE':
            '<?= GetMessageJS('DIGITMIND_REDIRECTURLWRITER_URLCOLLATION_PROD_URLS_FILE') ?>',
        'DIGITMIND_REDIRECTURLWRITER_URLCOLLATION_SECT_URLS_FILE':
            '<?= GetMessageJS('DIGITMIND_REDIRECTURLWRITER_URLCOLLATION_SECT_URLS_FILE') ?>',
        'DIGITMIND_REDIRECTURLWRITER_BAD_URLS_FILE':
            '<?= GetMessageJS('DIGITMIND_REDIRECTURLWRITER_BAD_URLS_FILE') ?>'
    });
</script>

<div class="wrapper">
    <?= Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_URLCOLLATION_PAGE_DESCRIPTION') ?>
</div>

<div class="wrapper">
    <input type="text" name="selected_file_path" id="selected_file_path" value="<?= $folderPath ?>" size="64"
           placeholder="<?= Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_URLCOLLATION_FILEPATH_PLACEHOLDER_TITLE') ?>"
           readonly
           required>
    <button id='open_file_dialog_button'>Открыть</button>
</div>

<input type="hidden" name="requested-page" id="requested-page" value="<?= $request->getRequestedPage() ?>">

<div class="wrapper">
    <div id="work-info-spinner"></div>
    <button id="start-work-button">
        <?= Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_URLCOLLATION_FILE_START_BUTTON') ?>
    </button>
</div>

<div class="wrapper">
    <div id="work-info"></div>
    <div id="product-without-old-url">
        <?php if (!empty($options[OPT_NAME_PRODUCTS_WITHOUT_OLD_URLS_FILE_PATH]['VALUE'])): ?>
            <a href="<?= $options[OPT_NAME_PRODUCTS_WITHOUT_OLD_URLS_FILE_PATH]['VALUE'] ?>" download>
                <?= Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_URLCOLLATION_PROD_URLS_FILE') ?>
            </a>
        <?php endif; ?>
    </div>
    <div id="section-without-old-url">
        <?php if (!empty($options[OPT_NAME_SECTIONS_WITHOUT_OLD_URLS_FILE_PATH]['VALUE'])): ?>
            <a href="<?= $options[OPT_NAME_SECTIONS_WITHOUT_OLD_URLS_FILE_PATH]['VALUE'] ?>" download>
                <?= Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_URLCOLLATION_SECT_URLS_FILE') ?>
            </a>
        <?php endif; ?>
    </div>
    <div id="bad-url">
        <?php if (!empty($options[OPT_NAME_BAD_URLS_FILE_PATH]['VALUE'])): ?>
            <a href="<?= $options[OPT_NAME_BAD_URLS_FILE_PATH]['VALUE'] ?>" download>
                <?= Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_BAD_URLS_FILE') ?>
            </a>
        <?php endif; ?>
    </div>
</div>

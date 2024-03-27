<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arAdminMenu = [
    'parent_menu' => 'global_menu_services',
    'sort' => 1000,
    'text' => Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_MENU_TEXT'),
    'title' => Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_MENU_TITLE'),
    'url' => '',
    'icon' => '',
    'page_icon' => '',
    'items_id' => 'digitmind_redirecturlwriter_menu',
    'items' => [
        [
            'text' => Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_MENU_XLSXPARSER_TEXT'),
            'title' => Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_MENU_XLSXPARSER_TITLE'),
            'url' => 'digitmind_redirecturlwriter_xlsxparse.php?lang=' . LANGUAGE_ID,
            'icon' => ''
        ],
        [
            'text' => Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_MENU_URLCOLLATION_TEXT'),
            'title' => Loc::getMessage('DIGITMIND_REDIRECTURLWRITER_MENU_URLCOLLATION_TITLE'),
            'url' => 'digitmind_redirecturlwriter_urlcollation.php?lang=' . LANGUAGE_ID,
            'icon' => ''
        ]
    ]
];

if (!empty($arAdminMenu)) {
    return $arAdminMenu;
} else {
    return false;
}

<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arAdminMenu = [
    'parent_menu' => 'global_menu_store',
    'sort' => 1000,
    'text' => Loc::getMessage('RESTART_REDIRECTURLWRITER_MENU_TEXT'),
    'title' => Loc::getMessage('RESTART_REDIRECTURLWRITER_MENU_TITLE'),
    'url' => '',
    'icon' => '',
    'page_icon' => '',
    'items_id' => 'restart_redirecturlwriter_menu',
    'items' => [
        [
            'text' => Loc::getMessage('RESTART_REDIRECTURLWRITER_SUBMENU_TEXT'),
            'title' => Loc::getMessage('RESTART_REDIRECTURLWRITER_SUBMENU_TITLE'),
            'url' => 'restart_redirecturlwriter_work.php?lang=' . LANGUAGE_ID,
            'icon' => ''
        ]
    ]
];

if (!empty($arAdminMenu)) {
    return $arAdminMenu;
} else {
    return false;
}

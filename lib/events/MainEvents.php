<?php

namespace Restart\RedirectUrlWriter\Events;

use Bitrix\Main\Loader;

Loader::includeModule('restart.redirecturlwriter');

class MainEvents
{
    public static function EpilogHandler()
    {
        //
    }
}

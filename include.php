<?php

use Bitrix\Main\Loader;

// При правильном именовании, классы подключаются автоматически. Имена файлов классов должны быть в нижнем регистре.
Loader::registerAutoloadClasses(
    'restart.redirecturlwriter',
    [
        'Restart\RedirectUrlWriter\Events\MainEvents' => 'lib/events/MainEvents.php',
        'Restart\RedirectUrlWriter\Helpers\MiscHelper' => 'lib/helpers/MiscHelper.php'
    ]
);

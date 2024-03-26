<?php

use Bitrix\Main\Loader;

// При правильном именовании, классы подключаются автоматически. Имена файлов классов должны быть в нижнем регистре.
Loader::registerAutoloadClasses(
    'digitmind.redirecturlwriter',
    [
        'DigitMind\RedirectUrlWriter\Events\MainEvents' => 'lib/events/MainEvents.php',
        'DigitMind\RedirectUrlWriter\Helpers\MiscHelper' => 'lib/helpers/MiscHelper.php'
    ]
);

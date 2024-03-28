<?php

use Bitrix\Main\Loader;

// При правильном именовании, классы подключаются автоматически. Имена файлов классов должны быть в нижнем регистре.
Loader::registerAutoloadClasses(
    'digitmind.redirecturlwriter',
    [
        'DigitMind\RedirectUrlWriter\Events\MainEvents' => 'lib/events/MainEvents.php',
        'DigitMind\RedirectUrlWriter\Helpers\MiscHelper' => 'lib/helpers/MiscHelper.php',
        'DigitMind\RedirectUrlWriter\Workers\Parser' => 'lib/workers/Parser.php',
        'DigitMind\RedirectUrlWriter\Workers\Collator' => 'lib/workers/Collator.php',
        'Shuchkin\SimpleXLSX' => 'lib/simplexslx/SimpleXLSX.php'
    ]
);

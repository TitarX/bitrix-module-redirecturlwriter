<?php

namespace DigitMind\RedirectUrlWriter\Helpers;

class MiscHelper
{
    public static function getModuleId()
    {
        return 'digitmind.redirecturlwriter';
    }

    public static function getAssetsPath($type)
    {
        $moduleId = self::getModuleId();
        $assetsPath = '';
        switch ($type) {
            case 'css':
            {
                $assetsPath = "/bitrix/css/{$moduleId}";
                break;
            }
            case 'js':
            {
                $assetsPath = "/bitrix/js/{$moduleId}";
                break;
            }
            case 'img':
            {
                $assetsPath = "/bitrix/images/{$moduleId}";
                break;
            }
        }
        return $assetsPath;
    }

    public static function getProgressBar($total, $value, $message)
    {
        $total = intval($total);
        $value = intval($value);
        $total1 = $total / 100;
        $progressValue = 100;
        if ($total1 > 0) {
            $progressValue = ($total - $value) / $total1;
        }

        \CAdminMessage::ShowMessage(
            [
                'MESSAGE' => $message,
                'DETAILS' => '' . '#PROGRESS_BAR#' . '',
                'HTML' => true,
                'TYPE' => 'PROGRESS',
                'PROGRESS_WIDTH' => '600',
                'PROGRESS_TOTAL' => 100,
                'PROGRESS_VALUE' => $progressValue
            ]
        );
    }

    public static function removeGetParameters($urlString)
    {
        $urlString = trim($urlString);
        list($path) = explode('?', $urlString);
        return $path;
    }
}

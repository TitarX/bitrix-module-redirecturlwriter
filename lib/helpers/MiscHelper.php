<?php

namespace DigitMind\RedirectUrlWriter\Helpers;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;

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

    public static function getModuleUploadDirPath()
    {
        $uploadDirectoryName = Option::get('main', 'upload_dir');
        $moduleId = GetModuleID(__FILE__);

        return "/{$uploadDirectoryName}/{$moduleId}";
    }

    public static function getModuleUploadDirFullPath()
    {
        $documentRoot = Application::getDocumentRoot();
        $moduleUploadDirPath = self::getModuleUploadDirPath();

        return "{$documentRoot}{$moduleUploadDirPath}";
    }

    public static function removeGetParameters($urlString)
    {
        $urlString = trim($urlString);
        list($path) = explode('?', $urlString);
        return $path;
    }

    /**
     * Проверка URL на ответ с кодом 200
     *
     * @param $url
     * @param $includeRedirects - С учётом редиректов
     *
     * @return ?bool
     */
    public static function checkUrl200($url, $includeRedirects = true)
    {
        $curl = curl_init($url);

        curl_setopt_array($curl, [
            CURLOPT_FOLLOWLOCATION => $includeRedirects,
            CURLOPT_NOBODY => true,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $curlExecResult = curl_exec($curl);

        curl_close($curl);

        if ($curlExecResult !== false) {
            $curlInfo = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($curlInfo == 200) {
                return true;
            } else {
                return false;
            }
        } else {
            return null;
        }
    }

    /**
     * Содержит ли строка заданные подстроки
     *
     * @param $text
     * @param $textPieces
     *
     * @return bool
     */
    public static function checkStringContains($text, $textPieces)
    {
        $result = false;

        foreach ($textPieces as $textPiece) {
            if (stripos($text, $textPiece) !== false) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * Содержит ли строка заданные подстроки по регулярным выражениям
     *
     * @param $text
     * @param $textPieces
     *
     * @return bool
     */
    public static function checkStringContainsRegex($text, $textPieces)
    {
        $result = false;

        foreach ($textPieces as $textPiece) {
            if (preg_match($textPiece, $text) === 1) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    public static function checkFirstSlash($text)
    {
        if (!empty($text)) {
            if (preg_match('/^(?:(http:\/\/)|(https:\/\/)|(\/))/ui', $text) !== 1) {
                $text = "/$text";
            }
        }

        return $text;
    }
}

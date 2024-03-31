<?php

namespace DigitMind\RedirectUrlWriter\Workers;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use DigitMind\RedirectUrlWriter\Helpers\MiscHelper;
use Shuchkin\SimpleXLSX;

class Parser
{
    private const IBLOCK_ID = 4;
    private const URL_COL_INDEX = 1;
    private const PRODUCT_URL_FEATURE_PATTERN = '|/products/\d+/(\d+)|uis';
    private const SECTION_URL_FEATURE_PATTERN = '|/products/(\d+)|uis';

    public static function parseXlsxAndWriteUrls(string $fullFilePath)
    {
        $result = [];

        $parseXlsxResult = self::parseXlsx($fullFilePath);
        if (is_array($parseXlsxResult)) {
            $writeOldUrlsResult = self::writeOldUrls($parseXlsxResult);
            if (is_array($writeOldUrlsResult)) {
                $result = $writeOldUrlsResult;
            } else {
                $result = 'writeoldurlserror';
            }
        } else {
            $result = 'xlsxparseerror';
        }

        return $result;
    }

    private static function parseXlsx(string $fullFilePath)
    {
        if ($simpleXLSX = SimpleXLSX::parse($fullFilePath)) {
            $productUrls = [
                'products' => [],
                'sections' => []
            ];

            foreach ($simpleXLSX->readRows() as $row) {
                if (preg_match(self::PRODUCT_URL_FEATURE_PATTERN, $row[self::URL_COL_INDEX], $matches) === 1) {
                    if (!empty($matches[1]) && empty($productUrls['products'][$matches[1]])) {
                        $productUrls['products'][$matches[1]] =
                            parse_url($row[self::URL_COL_INDEX], PHP_URL_PATH);
                    }
                } elseif (preg_match(self::SECTION_URL_FEATURE_PATTERN, $row[self::URL_COL_INDEX], $matches) === 1) {
                    if (!empty($matches[1]) && empty($productUrls['sections'][$matches[1]])) {
                        $productUrls['sections'][$matches[1]] =
                            parse_url($row[self::URL_COL_INDEX], PHP_URL_PATH);
                    }
                }
            }

            return $productUrls;
        } else {
            // $parseError =  SimpleXLSX::parseError();
            // file_put_contents(__DIR__ . '/error.txt', print_r($parseError, true));

            return 'xlsxparseerror';
        }
    }

    private static function writeOldUrls(array $oldUrls)
    {
        global $USER_FIELD_MANAGER;

        $result = false;
        $documentRoot = Application::getDocumentRoot();
        $moduleUploadDir = MiscHelper::getModuleUploadDirPath();
        $freeProductsUrlsFilePath = "{$moduleUploadDir}/free_products_urls.txt";
        $freeSectionsUrlsFilePath = "{$moduleUploadDir}/free_sections_urls.txt";

        if (Loader::includeModule('iblock')) {
            $productKeys = array_keys($oldUrls['products']);
            $dbResult = \CIBlockElement::GetList(
                ['SORT' => 'ASC'],
                [
                    'IBLOCK_ID' => self::IBLOCK_ID,
                    'ID' => $productKeys
                ],
                false,
                false,
                ['IBLOCK_ID', 'ID']
            );
            while ($arrResult = $dbResult->Fetch()) {
                \CIBlockElement::SetPropertyValuesEx(
                    $arrResult['ID'],
                    self::IBLOCK_ID,
                    [
                        'PRODUCT_OLD_URL' => $oldUrls['products'][$arrResult['ID']]
                    ]
                );

                unset($oldUrls['products'][$arrResult['ID']]);
            }
            file_put_contents(
                "{$documentRoot}{$freeProductsUrlsFilePath}",
                implode(PHP_EOL, $oldUrls['products'])
            );

            $sectionKeys = array_keys($oldUrls['sections']);
            $dbResult = \CIBlockSection::GetList(
                ['SORT' => 'ASC'],
                [
                    'IBLOCK_ID' => self::IBLOCK_ID,
                    'ID' => $sectionKeys
                ],
                false,
                ['IBLOCK_ID', 'ID'],
                false
            );
            while ($arrResult = $dbResult->Fetch()) {
                $USER_FIELD_MANAGER->Update(
                    'IBLOCK_' . self::IBLOCK_ID . '_SECTION',
                    $arrResult['ID'],
                    ['UF_SECTION_OLD_URL' => $oldUrls['sections'][$arrResult['ID']]]
                );

                unset($oldUrls['sections'][$arrResult['ID']]);
            }
            file_put_contents(
                "{$documentRoot}{$freeSectionsUrlsFilePath}",
                implode(PHP_EOL, $oldUrls['sections'])
            );

            $result = [
                'free_products_urls_file_path' => $freeProductsUrlsFilePath,
                'free_sections_urls_file_path' => $freeSectionsUrlsFilePath
            ];
        }

        return $result;
    }
}

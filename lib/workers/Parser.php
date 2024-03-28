<?php

namespace DigitMind\RedirectUrlWriter\Workers;

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
                //
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
                            MiscHelper::removeGetParameters($row[self::URL_COL_INDEX]);
                    }
                } elseif (preg_match(self::SECTION_URL_FEATURE_PATTERN, $row[self::URL_COL_INDEX], $matches) === 1) {
                    if (!empty($matches[1]) && empty($productUrls['sections'][$matches[1]])) {
                        $productUrls['sections'][$matches[1]] =
                            MiscHelper::removeGetParameters($row[self::URL_COL_INDEX]);
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
                // \CIBlockElement::SetPropertyValuesEx(
                //     $arrResult['ID'],
                //     self::IBLOCK_ID,
                //     [
                //         'PRODUCT_OLD_URL' => $oldUrls['products'][$arrResult['ID']]
                //     ]
                // );

                unset($oldUrls['products'][$arrResult['ID']]);
            }

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
                // $USER_FIELD_MANAGER->Update(
                //     'IBLOCK_4_SECTION',
                //     $arrResult['ID'],
                //     ['UF_SECTION_OLD_URL' => $oldUrls['sections'][$arrResult['ID']]]
                // );

                unset($oldUrls['sections'][$arrResult['ID']]);
            }
        }

        return $result;
    }
}

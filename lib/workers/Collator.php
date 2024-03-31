<?php

namespace DigitMind\RedirectUrlWriter\Workers;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\IO\File;
use DigitMind\RedirectUrlWriter\Helpers\MiscHelper;

class Collator
{
    private const IBLOCK_ID = 4;
    private const MAX_ENTITY_COUNT = 500;

    public static function collateUrls(string $csvFilePath)
    {
        $result = [];

        if (Loader::includeModule('iblock')) {
            $documentRoot = Application::getDocumentRoot();
            $moduleUploadDir = MiscHelper::getModuleUploadDirPath();

            $csvFilePathFull = "{$documentRoot}{$csvFilePath}";

            $productsWithoutOldUrlsFilePath = "{$moduleUploadDir}/products_without_old_urls_file_path.txt";
            $sectionsWithoutOldUrlsFilePath = "{$moduleUploadDir}/sections_without_old_urls_file_path.txt";
            $productsWithoutOldUrlsFilePathFull = "{$documentRoot}{$productsWithoutOldUrlsFilePath}";
            $sectionsWithoutOldUrlsFilePathFull = "{$documentRoot}{$sectionsWithoutOldUrlsFilePath}";

            $file = new File($csvFilePathFull);
            if ($file->isExists() && $file->isFile()) {
                $file->delete();
            }
            $file = new File($productsWithoutOldUrlsFilePathFull);
            if ($file->isExists() && $file->isFile()) {
                $file->delete();
            }
            $file = new File($sectionsWithoutOldUrlsFilePathFull);
            if ($file->isExists() && $file->isFile()) {
                $file->delete();
            }

            $nextId = 0;
            do {
                $nextId = self::collateElementsUrls($csvFilePathFull, $productsWithoutOldUrlsFilePathFull, $nextId);
            } while (!empty($nextId));

            self::collateSectionsUrls($csvFilePathFull, $sectionsWithoutOldUrlsFilePathFull);

            $file = new File($productsWithoutOldUrlsFilePathFull);
            if ($file->isExists() && $file->isFile()) {
                $result['products_without_old_urls_file_path'] = $productsWithoutOldUrlsFilePath;
            }

            $file = new File($sectionsWithoutOldUrlsFilePathFull);
            if ($file->isExists() && $file->isFile()) {
                $result['sections_without_old_urls_file_path'] = $sectionsWithoutOldUrlsFilePath;
            }
        } else {
            $result = 'iblockincludeerror';
        }

        return $result;
    }

    private static function collateElementsUrls(
        $csvFilePathFull,
        $extraElementPaths,
        $lastId = 0,
        $maxEntityCount = self::MAX_ENTITY_COUNT
    ) {
        $order = ['ID' => 'ASC'];

        $filter = [
            'IBLOCK_ID' => self::IBLOCK_ID,
            '>ID' => $lastId
        ];

        $group = false;

        $nav = ['nTopCount' => $maxEntityCount];

        $select = ['IBLOCK_ID', 'ID', 'DETAIL_PAGE_URL', 'PROPERTY_PRODUCT_OLD_URL'];

        $newLastId = 0;
        $arrCsvStrings = [];
        $extraElements = [];
        $dbResult = \CIBlockElement::GetList($order, $filter, $group, $nav, $select);
        while ($objResult = $dbResult->GetNextElement()) {
            $arrFields = $objResult->GetFields();
            $newLastId = $arrFields['ID'];

            if (!empty($arrFields['DETAIL_PAGE_URL'])) {
                if (!empty($arrFields['PROPERTY_PRODUCT_OLD_URL_VALUE'])) {
                    if ($arrFields['PROPERTY_PRODUCT_OLD_URL_VALUE'] != $arrFields['DETAIL_PAGE_URL']) {
                        $arrCsvStrings[] = [
                            $arrFields['PROPERTY_PRODUCT_OLD_URL_VALUE'],
                            $arrFields['DETAIL_PAGE_URL'],
                            '301'
                        ];
                    }
                } else {
                    $extraElements[] = $arrFields['DETAIL_PAGE_URL'];
                }
            }
        }

        if (!empty($arrCsvStrings)) {
            $fileRes = fopen($csvFilePathFull, 'a');

            foreach ($arrCsvStrings as $arrCsvString) {
                fputcsv($fileRes, $arrCsvString, ';');
            }

            fclose($fileRes);
        }

        if (!empty($extraElements)) {
            $extraElementsString = implode(PHP_EOL, $extraElements);
            file_put_contents($extraElementPaths, $extraElementsString, FILE_APPEND);
            file_put_contents($extraElementPaths, PHP_EOL, FILE_APPEND);
        }

        return $newLastId;
    }

    private static function collateSectionsUrls($csvFilePathFull, $extraSectionPaths)
    {
        $order = ['ID' => 'ASC'];
        $filter = ['IBLOCK_ID' => self::IBLOCK_ID];
        $cnt = false;
        $select = [
            'IBLOCK_ID',
            'ID',
            'IBLOCK_TYPE_ID',
            'IBLOCK_SECTION_ID',
            'CODE',
            'UF_SECTION_OLD_URL',
            'SECTION_PAGE_URL'
        ];

        $arrCsvStrings = [];
        $extraSections = [];
        $dbResult = \CIBlockSection::GetList($order, $filter, $cnt, $select);
        while ($objResult = $dbResult->GetNextElement()) {
            $arrFields = $objResult->GetFields();

            if (!empty($arrFields['SECTION_PAGE_URL'])) {
                if (!empty($arrFields['UF_SECTION_OLD_URL'])) {
                    if ($arrFields['UF_SECTION_OLD_URL'] != $arrFields['SECTION_PAGE_URL']) {
                        $arrCsvStrings[] = [
                            $arrFields['UF_SECTION_OLD_URL'],
                            $arrFields['SECTION_PAGE_URL'],
                            '301'
                        ];
                    }
                } else {
                    $extraSections[] = $arrFields['SECTION_PAGE_URL'];
                }
            }
        }

        if (!empty($arrCsvStrings)) {
            $fileRes = fopen($csvFilePathFull, 'a');

            foreach ($arrCsvStrings as $arrCsvString) {
                fputcsv($fileRes, $arrCsvString, ';');
            }

            fclose($fileRes);
        }

        if (!empty($extraSections)) {
            $extraSectionsString = implode(PHP_EOL, $extraSections);
            file_put_contents($extraSectionPaths, $extraSectionsString, FILE_APPEND);
            file_put_contents($extraSectionPaths, PHP_EOL, FILE_APPEND);
        }
    }
}

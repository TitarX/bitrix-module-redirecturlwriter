<?php

namespace DigitMind\RedirectUrlWriter\Entities;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class OptionsTable extends Entity\DataManager
{
    /**
     * Метод возвращает имя таблицы
     *
     * @access public
     * @static
     *
     * @return string Имя таблицы
     */
    public static function getTableName(): string
    {
        return 'digitmind_redirecturlwriter_options';
    }

    /**
     * Метод возвращает карту полей таблицы базы данных
     *
     * @access public
     * @static
     *
     * @return array Массив объектов, описывающих поля таблицы в базе данных
     * @throws SystemException
     */
    public static function getMap(): array
    {
        return [
            new Entity\IntegerField(
                'ID',
                [
                    'primary' => true,
                    'autocomplete' => true
                ]
            ),
            new Entity\TextField(
                'CODE',
                [
                    'required' => true
                ]
            ),
            new Entity\TextField(
                'VALUE',
                [
                    'serialized' => true,
                    'default_value' => ''
                ]
            )
        ];
    }

    /**
     * @return array
     *
     * @throws SystemException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     */
    public static function getData(): array
    {
        $result = [];

        $dbResult = OptionsTable::getList([
            'select' => ['ID', 'CODE', 'VALUE']
        ]);
        while ($arrResult = $dbResult->fetch()) {
            $result[$arrResult['CODE']] = [
                'ID' => $arrResult['ID'],
                'VALUE' => $arrResult['VALUE']
            ];
        }

        return $result;
    }
}

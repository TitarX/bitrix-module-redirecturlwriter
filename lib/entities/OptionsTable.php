<?php

namespace DigitMind\RedirectUrlWriter\Entities;

use Bitrix\Main\Entity;
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
                    'required' => true
                ]
            )
        ];
    }
}

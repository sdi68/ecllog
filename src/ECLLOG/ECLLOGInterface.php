<?php

namespace ECLLOG;

/**
 * @version 1.0.1
 */
interface ECLLOGInterface
{
    /**
     * Массив корректных значений типа записи
     * @var array
     * "@since 1.0.1
     */
    const _validMsgTypes = array();

    /**
     * Формирует путь к файлу лога
     * @return string
     * @since      1.0.0
     */
    static function getLogPath():string;

    /**
     * Определяет разрешено или нет логирование
     * @return bool
     * @since      1.0.0
     */
    static function checkEnabled():bool;

    /**
     * Преобразует переменную для записи в строку
     * @param mixed $data Переменная
     * @return string
     * @since      1.0.0
     */
    static function prepareData($data):string;

    /**
     * Формирует отметку времени записи лога
     * @return string
     * @since      1.0.0
     */
    static function addTimestamp():string;

    /**
     * Проверяет корректность указания типа записи (например, ошибка, предупреждение и т.п.)
     * @param string $type Название типа записи
     * @return string
     * @since      1.0.0
     */
    static function validateLogType(string $type):string;
}
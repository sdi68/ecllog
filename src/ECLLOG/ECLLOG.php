<?php
/**
 *
 * @package         ECLLOG
 * @version           1.0.2
 * @author            ECL <info@econsultlab.ru>
 * @link                 https://econsultlab.ru
 * @copyright       Copyright © 2024 ECL All Rights Reserved
 * @license           http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace ECLLOG;

/**
 * Базовый класс логирования
 * @since  1.0.0
 */
abstract class ECLLOG
{

    /**
     * Ошибка.
     *
     * @var    string
     * @since  1.0.2
     */
    const ERROR = "error";

    /**
     * Предупреждение.
     *
     * @var    string
     * @since  1.0.2
     */
    const WARNING = "warning";

    /**
     * Информация.
     *
     * @var    string
     * @since  1.0.2
     */
    const INFO = "info";

    /**
     * The global ECLLOG instance.
     *
     * @var    ECLLOG
     * @since  1.0.1
     */
    protected static $instance;

    /**
     * Массив логгеров, отвечающих за различные приложения,
     * использующие логирование
     *
     * @var    ECLLogger[]
     * @since  1.0.2
     */
    protected static $loggers = [];

    /**
     * Конструктор
     * @since  1.0.2
     */
    public function __construct()
    {

    }

    /**
     * Returns a reference to the a ECLLOG object, only creating it if it doesn't already exist.
     *
     * @param ECLLOG $instance The ECLLOG object instance to be used by the static methods.
     *
     * @return  void
     *
     * @since   1.0.2
     */
    public static function setInstance($instance): void
    {
        if (($instance instanceof ECLLOG) || $instance === null) {
            static::$instance = &$instance;
        }
    }

    /**
     * @param string $type Тип записи. Одно из значений:
     * Message - информационное сообщение
     * Warning - предупреждение
     * Error - ошибка
     * @param string $source Название инициатора лога
     * @param string $message Текстовое описание
     * @param mixed|null $data Данные (переменная) для записи в лог
     * @return void
     * @since      1.0.2
     */
    protected static function _add(string $source, string $type, string $message, $data = null)
    {
        static::$loggers[$source]->addEntry($type, $message, $data);
    }
}
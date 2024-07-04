<?php
/**
 *
 * @package         ECLLOG
 * @version         1.0.2
 * @author          ECL <info@econsultlab.ru>
 * @link            https://econsultlab.ru
 * @copyright       Copyright © 2024 ECL All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
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
     * Добавляет запись в лог
     * @param array $options Настройки логирования
     * $options['enabled'] - bool - разрешена или нет запись в лог
     * $options['source'] - string - наименование компонента источника лога
     * $options['logger'] - string - класс логгера (с пространством имен)
     * $options['back_trace_level'] - int - число уровней в debug_backtrace от функции, создающей запись в логе до текущей функции
     * @param array $data Данные для записи в лог
     * --- Обязательные поля ---
     * $data['timestamp'] - string метка времени или пустое значение, чтоб сгенерировать в логгере
     * $data['type'] - string тип записи из перечня ECLLOG::INFO, ECLLOG::ERROR, ECLLOG::WARNING
     * $data['caller'] - string вызывающая запись процедура или если пустое, то определяется через debug_backtrace
     * $data['message'] - string сообщение для вывода в лог
     * $data['data'] - mixed значение переменной для вывода в лог
     * --- Дополнительные поля ---
     * Определяются для конкретного типа логгера
     * @return void
     * @since   1.0.2
     */
    protected static function _add(array $options, array $data): void
    {
        static::$loggers[$options["source"]]->addEntry($data);
    }

    /**
     * Проверка возможности вывода записи в лог
     * @param array $options Настройки логирования
     * @return bool
     * @since   1.0.2
     */
    protected static function _checkLogEnabled(array $options): bool
    {
        $ret = $options["enabled"] ?? false;
        $ret &= !empty($options["source"]);
        $ret &= isset($options["logger"]) && class_exists($options["logger"]);
        return $ret;
    }
}
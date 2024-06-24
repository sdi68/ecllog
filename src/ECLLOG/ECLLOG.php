<?php

namespace ECLLOG;


/**
 * Класс создания лога приложения
 *
 * @version 1.0.0
 */
class ECLLOG
{
    /**
     * @param string $type Тип записи. Одно из значений:
     * Message - информационное сообщение
     * Warning - предупреждение
     * Error - ошибка
     * @param string $description Текстовое описание
     * @param mixed|null $data Данные (переменная) для записи в лог
     * @return void
     * @since      1.0.1
     */
    static final function storeLog(string $type, string $description, $data = null): void
    {

        if(call_user_func( array( get_called_class(), 'checkEnabled' ))) {

            $type = call_user_func( array( get_called_class(), 'validateLogType',$type));

            // Add timestamp to the entry
            $entry = '[' . call_user_func( array( get_called_class(), 'addTimestamp')) . '] - ' . $type . ' - ' . self::_getCaller() . ' - ' . $description . (is_null($data) ? '' : ' - ' . call_user_func( array( get_called_class(), 'getLogPath',$data ))) . "\n";

            // Compute the log file's path.
            static $path;
            if (!$path) {
                $path = call_user_func( array( get_called_class(), 'getLogPath' ));
            }
            file_put_contents($path, $entry, FILE_APPEND);
        }
    }

    /**
     * Получает объект и функцию, откуда вызван лог
     * @return mixed|string
     * @since      1.0.1
     */
    private static final function _getCaller():string
    {
        $trace = debug_backtrace();
        $caller = $trace[2];
        if (isset($caller['class'])) {
            return $caller['class'] . ':' . $caller['function'];
        } else {
            return $caller['function'];
        }
    }
}
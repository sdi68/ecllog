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
 * Прототип класса логгера. Переопределяются для конкретных СМС или для приложений
 * @since 1.0.2
 */
abstract class ECLLogger
{

    /**
     * Путь к файлу лога
     * @var string
     * @since 1.0.2
     */
    protected $path = "";

    /**
     * Уровень в debug_backtrace до функции, создающей запись в логе
     * @var int
     * @since 1.0.2
     */
    protected $back_trace_level = 4;

    /**
     * Настройки логирования
     * @var array
     * @since 1.0.2
     */
    protected $options = array();

    /**
     * Обязательные общие поля записи лога
     * ключ - поле шаблона
     * значение - функция получения значения поля
     * @var string[]
     * @since 1.0.2
     */
    private $default_fields = array(
        "timestamp" => "getTimeStamp",
        "type" => "getType",
        "caller" => "getCaller",
        "message" => "getMessage",
        "data" => "getData"
    );

    /**
     * Дополнительные поля записи лога (задаются в конкретном логгере)
     * ключ - поле шаблона
     * значение - функция получения значения поля
     * @var string[]
     * @since 1.0.2
     */
    protected $advanced_fields = array();

    /**
     * Значения полей записи
     * ключ - поле шаблона
     * значение - значение поля
     * @var array
     * @since 1.0.2
     */
    protected $filds_values = array();

    /**
     * Шаблон записи лога.
     * Поля заключаются в {}
     * Может переопределяться в конкретном логгере
     * @var string
     * @since 1.0.2
     */
    protected $entry_format = "[{timestamp}] - {type} - {caller} - {message} - {data}";

    /**
     * Конструктор
     * @param array $options Параметры логгера
     * @since 1.0.2
     */
    public function __construct(array $options)
    {
        $this->options = $options;

        if (empty($this->path)) {
            $this->path = $this->setPathFromSource($options['source']);
        }
        // Получаем уровень до функции источника данных в логе
        $this->back_trace_level = $options["back_trace_level"] + 5;
    }

    /**
     * Получает массив обязательных полей
     * @return string[]
     * @since 1.0.2
     */
    public function getDefaultFields(): array
    {
        return $this->default_fields;
    }

    /**
     * @param array $data
     * @return void
     * @since 1.0.2
     */
    public final function addEntry(array $data): void
    {
        if ($this->checkDataFields($data)) {
            $line = "";
            $is_new = !file_exists($this->path);
            if ($is_new) {
                // Файла нет - инициализируем создание
                $line = $this->initFile();
            }

            $line .= $this->generateEntryLine($data);
            $result = file_put_contents($this->path, $line, $is_new ? 0 : FILE_APPEND);
            if ($result === false) {
                throw new \RuntimeException('Cannot write to log file.');
            }
        }

    }

    /**
     * Проверка наличия данных для вывода в лог
     * @param array $data Данные для вывода в лог
     * @return bool
     * @since 1.0.2
     */
    protected final function checkDataFields(array $data): bool
    {
        $ret = true;
        foreach ($this->default_fields as $field => $func) {
            if (!isset($data[$field])) {
                throw new \RuntimeException(sprintf('Not present %s required field.', $field));
            }
        }
        $ret &= $this->checkAdvancedDataFields($data);
        return $ret;
    }

    /**
     * Проверка наличия данных дополнительных полей для вывода в лог
     * Может переопределяться в логгере
     * @param array $data Данные для вывода в лог
     * @return bool
     * @since 1.0.2
     */
    protected function checkAdvancedDataFields(array $data): bool
    {
        if (count($this->advanced_fields)) {
            foreach ($this->advanced_fields as $field => $func) {
                if (!isset($data[$field])) {
                    throw new \RuntimeException(sprintf('Not present %s advanced field.', $field));
                }
            }
        }
        return true;
    }

    /**
     * Формирует массив значений полей для подстановки в запись
     * @param array $data Данные для записи в лог
     * @return void
     * @since 1.0.2
     */
    private function getFieldsValues(array $data): void
    {
        foreach ($data as $field => $val) {
            if (isset($this->default_fields[$field])) {
                // Проверяем основные поля
                if (!method_exists($this, $this->default_fields[$field])) {
                    throw new \RuntimeException(sprintf('Not present default field method %s.', $this->default_fields[$field]));
                }
                $this->filds_values[$field] = call_user_func(array($this, $this->default_fields[$field]), $val);
            } else if (isset($this->advanced_fields[$field])) {
                // Проверяем дополнительные поля
                if (!method_exists($this, $this->advanced_fields[$field])) {
                    throw new \RuntimeException(sprintf('Not present advanced field method %s.', $this->advanced_fields[$field]));
                }
                $this->filds_values[$field] = call_user_func(array($this, $this->advanced_fields[$field]), $val);
            } else {
                // неизвестное поле
                throw new \RuntimeException(sprintf('Unknown field %s.', $field));
            }
        }
    }

    /**
     * Формирует строку записи данных в лог
     * Заменяет поля шаблона на значения
     * @param array $data Данные для записи в лог
     * @return string
     */
    protected final function generateEntryLine(array $data): string
    {
        try {
            $this->getFieldsValues($data);
        } catch (\RuntimeException $e) {
            return $e->getMessage() . "\n";
        }
        $fields = array();
        $vals = array();
        foreach ($this->filds_values as $field => $val) {
            $fields[] = "{" . $field . "}";
            $vals[] = $val;
        }
        return str_replace($fields, $vals, $this->entry_format) . "\n";
    }


    /**
     * Инициализация файла лога. Возвращает текст заголовка файла.
     * Может быть переопределена в логгере.
     * @return string
     * @since 1.0.2
     */
    protected function initFile(): string
    {
        return $this->generateFileHeader();
    }

    /**
     * Формирует значение типа записи в лог
     * @param string $type Исходный тип записи
     * Может быть переопределена в логгере.
     *
     * @return string
     * @since 1.0.2
     */
    protected function getType(string $type): string
    {
        $check = array(ECLLOG::INFO, ECLLOG::ERROR, ECLLOG::WARNING);
        return (in_array($type, $check) ? $type : ECLLOG::INFO);
    }

    /**
     * Формирует название вызывающей процедуры записи в лог
     * @param string $caller Вызывающая процедура
     * Может быть переопределена в логгере.
     *
     * @return string
     * @since 1.0.2
     */
    protected function getCaller(string $caller): string
    {
        if (!empty($caller))
            return $caller;

        $trace = debug_backtrace();
        $caller = $trace[$this->back_trace_level];
        if (isset($caller['class'])) {
            return $caller['class'] . ':' . $caller['function'];
        } else {
            return $caller['function'] ?? "";
        }
    }

    /**
     * Формирует значение сообщения в лог
     * @param string $message Сообщение
     * Может быть переопределена в логгере.
     *
     * @return string
     * @since 1.0.2
     */
    protected function getMessage(string $message): string
    {
        return $message;
    }

    /**
     * Формирует значение переменной для записи в лог
     * @param mixed $data Переменная
     * Может быть переопределена в логгере.
     *
     * @return string
     * @since 1.0.2
     */
    protected function getData(mixed $data): string
    {
        if (empty($data))
            return '';

        return str_replace(array(' ', "\r\n", "\n", "\r"), '', print_r($data, true));
    }

    /**
     * Формирует путь к файлу лога приложения
     * @param string $source Наименование приложения источника логов
     * @return string
     * @since 1.0.2
     */
    abstract protected function setPathFromSource(string $source): string;

    /**
     * Формирует заголовок файла лога при его создании
     * @return string
     * @since 1.0.2
     */
    abstract protected function generateFileHeader(): string;

    /**
     * Формирует значение отметки времени для записи в лог
     * @param string $timestamp Отметка времени. Если пустая, то формируется логгером.
     *
     * @return string
     * @since 1.0.2
     */
    abstract protected function getTimeStamp(string $timestamp): string;
}

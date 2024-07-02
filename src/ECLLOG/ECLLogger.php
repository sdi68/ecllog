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
     * Уровень в bactrace до функции, создающей запись в логе
     * @var int
     * @since 1.0.2
     */
    protected $back_trace_level = 4;

    /**
     * Формат записи строки в лог
     * @var string
     * @since 1.0.2
     */
    protected $format = "[{time_stamp}] - {entry_type} - {caller} - {description} - {data}";

    /**
     * Переменные шаблона
     * @var string[]
     * @since 1.0.2
     */
    protected $format_variables = array(
        "{time_stamp}",
        "{entry_type}",
        "{caller}",
        "{description}",
        "{data}"
    );


    /**
     * Конструктор
     * @param string $source Наименование приложения источника лога
     * @since 1.0.2
     */
    public function __construct(string $source)
    {
        if (empty($this->path)) {
            $this->setPathFromSource($source);
        }
    }


    /**
     * Устанавливает значение пути к файлу лога
     * @param string|null $path
     * @since 1.0.2
     */
    protected function _setPath(?string $path): void
    {
        $this->path = $path;
    }

    /**
     * Формирует путь к файлу лога приложения
     * @param string $source Наименование приложения источника логов
     * @return string
     * @since 1.0.2
     */
    abstract protected function setPathFromSource(string $source): string;

    /**
     * Формирует метку времени записи лога
     * @return string
     * @since 1.0.2
     */
    abstract protected function addTimestamp(): string;

    /**
     * Преобразует значение переменной для записи в строку лога
     * @param mixed $data
     * @return string
     * @since 1.0.2
     */
    abstract protected function prepareData($data): string;

    /**
     * Формирует заголовок файла лога при его создании
     * @return string
     * @since 1.0.2
     */
    abstract protected function generateFileHeader(): string;

    /**
     * Собирает строку данных для записи в лог
     * @param string $type Тип записи (ECLLOG::ERROR ECLLOG::INFO и т.п.)
     * @param string $message Сообщение для записи в лог
     * @param mixed $data Переменная для записи в лог
     * @return string
     * @since 1.0.2
     */
    abstract protected function generateEntryLine(string $type, string $message, $data = null): string;

    /**
     * Добавить запись в лог
     * @param string $type Тип записи (ECLLOG::ERROR ECLLOG::INFO и т.п.)
     * @param string $message Сообщение для записи в лог
     * @param mixed $data Переменная для записи в лог
     * @return void
     * @since 1.0.2
     */
    public function addEntry(string $type, string $message, $data = null)
    {

        $line = "";;
        if (!file_exists($this->path)) {
            // Файла нет - инициализируем создание
            $line = $this->initFile();
        }

        $line .= $this->generateEntryLine($type, $message, $data);

        if (!file_put_contents($this->path, $line, FILE_APPEND)) {
            throw new \RuntimeException('Cannot write to log file.');
        }

    }

    /**
     * Инициализация файла лога. Возвращает текст заголовка файла.
     * @return string
     * @since 1.0.2
     */
    protected function initFile(): string
    {
        return $this->generateFileHeader();
    }

    /**
     * Добавляет в строку лога валидный тип записи
     * @param string $type Тип записи
     * @return string
     * @since 1.0.2
     */
    protected function addEntryType(string $type): string
    {
        $check = array(ECLLOG::INFO, ECLLOG::ERROR, ECLLOG::WARNING);
        return (in_array($type, $check) ? $type : ECLLOG::INFO);
    }

    /**
     * Получает объект и функцию, откуда вызван лог
     * @return string
     * @since      1.0.1
     */
    protected function getCaller(): string
    {
        $trace = debug_backtrace();
        $caller = $trace[$this->back_trace_level];
        if (isset($caller['class'])) {
            return $caller['class'] . ':' . $caller['function'];
        } else {
            return $caller['function'] ?? "";
        }
    }

}
# PHP-библиотека логирования данных

Библиотека для организации логирования работы компонентов различных CMS.

Библиотека написана на языке PHP и содержит классы для организации логирования.

* [Требования](#Требования)
* [Лицензия и условия использования](#Лицензия-и-условия-использования)
* [Установка](#Установка)
* [Пример использования](#Пример-использования)

## Требования

* PHP 7.4 или выше.

## Лицензия и условия использования

Библиотека распространяется по [лицензии MIT](LICENSE).

## Установка

Библиотека устанавливается с помощью пакетного менеджера [Composer](https://getcomposer.org).

1. Добавьте библиотеку в файл `composer.json` вашего проекта:

   ```json
   {
       "require": {
           "sdi68/ecllog": "*"
       }
   }
   ```

2. Включите автозагрузчик Composer в код проекта:

   ```php
   require __DIR__ . '/vendor/autoload.php';
   ```   

## Пример использования

1. Создаем класс, потомок ECLLOG и логгера ECLLogger и переопределяем методы в соответствии со своими потребностями:

```php
class ECLLogging extends ECLLOG{

    public static function add(bool $enabled,string $source, string $type, string $message, $data = null): void
    {
        self::$logger_class = "ECLabs\\Library\\Loggers\\ECLabLogger";
        if($enabled) {
            if (!isset(self::$loggers[$source])) {
                self::$loggers[$source] = new self::$logger_class($source);
            }
            self::_add($source, $type, $message, $data);
        }
    }

}

class ECLabLogger extends ECLLogger
{
    public function __construct(string $source)
    {
        $this->back_trace_level = 4;

        $this->format = "[{time_stamp}] - {entry_type} - {caller} - {description} - {data}";
        $this->format_variables = array(
            "{time_stamp}",
            "{entry_type}",
            "{caller}",
            "{description}",
            "{data}");
        parent::__construct($source);
    }
    
        protected function setPathFromSource(string $source): void
    {
		$config = new JConfig();
		$path = $config->log_path. '/' . $source . '.php';
        $this->_setPath($path);
    }

    protected function addTimestamp(): string
    {
        return Factory::getDate()->format('Y-m-d H:i:s');
    }

    protected function generateFileHeader(): string
    {
        $head = array();
        $head[] = '#';
        $head[] = '#<?php die(\'Forbidden.\'); ?>';
        $head[] = '#Date: ' . gmdate('Y-m-d H:i:s') . ' UTC';
        $version = new Version();
        $head[] = '#Software: ' . $version->getLongVersion();
        $head[] = '';

        // Prepare the fields string
        $head[] = '#Fields: ' . implode(" - ",$this->format_variables);
        $head[] = '';

        return implode("\n", $head);
    }

    protected function prepareData(mixed $data): string
    {
        if (empty($data))
			return '';
		return str_replace(array(' ', "\r\n", "\n", "\r"), '', print_r($data, true));
    }

    protected function generateEntryLine(string $type, string $message, $data = null): string
    {
        return str_replace(
            $this->format_variables,
            array(
                $this->addTimestamp(),
                $this->addEntryType($type),
                $this->getCaller(),
                $message,
                $this->prepareData($data)
            ),
            $this->format)."\n";
    }
}

```


2. В коде добавляем в нужном месте
```php
ECLLogging::add(true, $name,ECLLOG::INFO,"test",$data);;
```
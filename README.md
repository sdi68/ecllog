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
class ECLLogging extends ECLLOG
{
    public static function add(array $options, array $data = null): void
    {
        if (self::_checkLogEnabled($options))
        {
            if (!isset(self::$loggers[$options['source']]))
            {
                self::$loggers[$options['source']] = new $options['logger']($options);
            }
            self::_add($options, $data);
        }
    }
}

class ECLabDefaultLogger extends ECLLogger
{
    /**
     * @inheritDoc
     * @since       1.0.20
     */
    public function __construct(array $options)
    {
        parent::__construct($options);
        $this->entry_format = "[{timestamp}] - {type} - {caller} - {message} - {data}";
        $this->advanced_fields=array();
    }

    /**
     * @inheritDoc
     * @since       1.0.20
     */
    protected function setPathFromSource(string $source): string
    {
        $config = new JConfig();
        return $config->log_path . '/' . $source . '.php';
    }


    /**
     * @inheritDoc
     * @since       1.0.20
     */
    protected function generateFileHeader(): string
    {
        $head    = array();
        $head[]  = '#';
        $head[]  = '#<?php die(\'Forbidden.\'); ?>';
        $head[]  = '#Date: ' . gmdate('Y-m-d H:i:s') . ' UTC';
        $version = new Version();
        $head[]  = '#Software: ' . $version->getLongVersion();
        $head[]  = '';

        // Prepare the fields string
        $fields = array_merge($this->getDefaultFields(), $this->advanced_fields);
        $fields_names = array();
        foreach ($fields as $key => $val){
            $fields_names[] =  $key;
        }
        $head[] = '#Fields: ' . implode(" - ", $fields_names);
        $head[] = '';

        return implode("\n", $head);
    }

    protected function getTimeStamp($timestamp): string
    {
        if(!empty($timestamp))
            return $timestamp;

        return Factory::getDate()->format('Y-m-d H:i:s');
    }
}

```


2. В коде добавляем в нужном месте
```php
        ECLLogging::add(
            array(
                "source"=>$name,
                "enabled" =>$enabled,
                "logger" => "ECLabs\\Library\\ECLLogging\\Loggers\\ECLabDefaultLogger",
                "back_trace_level" => 4
            ),
            array(
                "timestamp" => "",
                "type" =>ECLLOG::INFO,
                "caller" => "",
                "message" =>"test",
                "data" => $data,
            )
        );
```
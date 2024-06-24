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

Библиотека распространяется по [лицензии MIT](LICENSE.txt).

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

1. Создаем класс, потомок ECLLOG и интерфейса ECLLOGInterface и переопределяем методы в соответствии со своими потребностями:

```php
use ECLLOG\ECLLOG;
use ECLLOG\ECLLOGInterface;

class WPLog extends ECLLOG implements ECLLOGInterface {
    static function getLogPath():string{
        return  plugin_dir_path(dirname(__FILE__)) . '/logs/WPLog.log';
    };
    static function checkEnabled():bool {
        $enabled= ...
        return $enabled;
    }
    
    static function prepareData($data):string{
        if (empty($data))
            return '';
        return str_replace(array(' ', "\r\n", "\n", "\r"), '', print_r($data, true));
    }
    
    static function addTimestamp():string{
        return date_i18n('Y-m-d H:i:s');
    }
    
    static function validateLogType(string $type):string{
          $types = array('Message', 'Warning', 'Error');

        if (in_array($type, $types) === false) {
            $type = 'Message';
        }
        return $type;
    }
}
```
2. В коде добавляем в нужном месте
```php
LOG::storeLog('Message', 'data', $data);
```
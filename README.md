## Конфиг

 - `@` в начале имени ссылка на сервис.
 - `@@` Указывает на то, что необходимо заменить указанный сервис.


```php
Пример конфига (больше примеров можно посмотреть в unit тестах):

'test.replace' => [
    'class' => TestClass::class
],
TestDefClass::class => [
    'class' => TestDefClass::class,
],
'@@' . TestDefClass::class => [
    'closure' => static function () {
        $t = new TestDefClass();
        $t->test = '666';

        return $t;
    }
]
```
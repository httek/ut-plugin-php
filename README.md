# zw-ut-plugin-php

用户标签SQL生成器

## 安装
~~~
composer require zhangwen/ut-plugin-php
~~~

## 用法
~~~php
use Zw\Plugin\Ut\SqlBuilder;

$builder = new SqlBuilder();

$meta = [
   'field' => 'sex',
   'calc' => 'in',
   'value' => [1],
];

$ruleClass = $builder->getClassFromField($meta['field'] ?? '');
$builder->addRule(new $class($meta), $valid = true);
$sql = $builder
    ->setClickHouseMode()
    // ->setMySqlMode()
    ->toSql();
 
echo $sql, PHP_EOL;   
~~~
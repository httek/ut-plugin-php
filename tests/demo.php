<?php

require __DIR__ . '/../vendor/autoload.php';

use Zw\Plugin\Ut\SqlBuilder;

$rules = [
    [
        'field' => 'sex',
        'calc' => 'in',
        'value' => [1],
    ],
    [
        'field' => 'os',
        'calc' => 'in',
        'value' => [1,2],
    ],
    [
        'field' => 'subscribe',
        'calc' => 'notIn',
        'value' => [0],
    ],
    [
        'field' => 'region',
        'calc' => 'in',
        'value' => ['北京市', '北京市'],
    ],
    [
        'field' => 'userLevel',
        'calc' => 'in',
        'value' => [2],
    ],
    [
        'field' => 'subscribeAt',
        'calc' => 'between',
        'value' => [1, 3],
        'type' => 1,
        'unit' => 'hour'
    ],
    [
        'field' => 'registeredAt',
        'calc' => 'between',
        'value' => [1, 4],
        'type' => 1,
        'unit' => 'hour'
    ],
    [
        'field' => 'userFavorite',
        'calc' => 'between',
        'value' => [1, 10],
        'type' => 1
    ],
];

$builder = new SqlBuilder();

foreach ($rules as $meta) {
    $class = $builder->getClassFromField($meta['field'] ?? '');
    $builder->addRule(new $class($meta));
}

$sql = $builder
    ->setClickHouseMode()
    ->toSql();

echo $sql, PHP_EOL;

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
        'field' => 'activateAt',
        'calc' => 'between',
        'value' => [1, 4],
        'type' => 1,
        'unit' => 'hour'
    ],
    [
        'field' => 'interactAt',
        'calc' => 'between',
        'value' => [1, 4],
        'type' => 1,
        'unit' => 'hour'
    ],
    [
        'field' => 'paidAt',
        'calc' => 'between',
        'value' => [1, 4],
        'type' => 1,
        'unit' => '-'
    ],
    [
        'field' => 'paidAt',
        'calc' => 'between',
        'value' => [1, 4],
        'type' => 1,
        'unit' => 'hour'
    ],
    // paidStatus
    [
        'field' => 'paidStatus',
        'calc' => 'in',
        'value' => [1],
        'type' => 1,
        'unit' => 'hour'
    ],
    [
        'field' => 'paidBooks',
        'calc' => 'in',
        'value' => [69975, 10068826],
    ],
    [
        'field' => 'consumeBooks',
        'calc' => 'notIn',
        'value' => [69975, 10068826],
    ],
    [
        'field' => 'bookCategories',
        'calc' => 'in',
        'value' => [8,3,23],
    ],
    [
        'field' => 'userBalance',
        'calc' => 'between',
        'value' => [50, 100, 200],
        'unit' => '',
        'type' => 1
    ],
    [
        'field' => 'paidAmount',
        'calc' => 'between',
        'value' => [50, 100, 200],
        'unit' => '',
        'type' => 2
    ],
    [
        'field' => 'paidTotal',
        'calc' => 'in',
        'value' => [50, 200],
        'unit' => '',
        'type' => 0
    ],
    [
        'field' => 'consumeAmount',
        'calc' => 'between',
        'value' => [50, 200],
        'unit' => '',
        'type' => 2
    ],
    [
        'field' => 'consumeStatus',
        'calc' => 'in',
        'value' => [1, 2],
    ],
    [
        'field' => 'userReadHistories',
        'calc' => 'notIn',
        'value' => [10069975, 10069932],
    ]
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

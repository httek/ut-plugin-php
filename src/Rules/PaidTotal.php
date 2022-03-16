<?php

namespace Zw\Plugin\Ut\Rules;

use Zw\Plugin\Ut\Rule;
use Illuminate\Support\Str;
use Zw\Plugin\Ut\SqlBuilder;

/**
 * @rule paidTotal: 充值次数： 0 全部， 1 三天内， 2 7天内， 3 14天内， 4 30天内
 */
class PaidTotal extends Rule
{
    /**
     * @var array|string[]
     */
    protected $validationRules = [
        'field' => 'require|eq:paidTotal', 'calc' => 'require|in:in,between', 'value' => 'require|array|min:1', 'type' => 'require|in:0,1,2,3,4'
    ];

    /**
     * @param array $meta
     * @param string $tablePrefix
     */
    public function __construct(array $meta, string $tablePrefix = '')
    {
        $this->meta = $meta;
        if ($tablePrefix) {
            $this->setTableAlias($tablePrefix);
        }
    }

    /**
     * @var array|string[]
     */
    protected $typeMaps = [
        'order_count_all', 'order_count_3', 'order_count_7', 'order_count_14', 'order_count_30'
    ];

    /**
     * @param int $dbmsType
     * @return string
     */
    public function getSqlSegment(int $dbmsType = SqlBuilder::MySQL): string
    {
        $type = $this->getMeta('type');
        if (empty($this->typeMaps[$type])) {
            return '/** paidTotal not in types:0,1,2,3,4 */';
        }

        $field = $this->getField($this->typeMaps[$type]);
        if (Str::contains("BETWEEN", $this->getClac())) {
            [$a, $b] = $this->getValues();

            return join(' ', [$field, $this->getClac(), "{$a} AND {$b}"]);
        }

        return join(' ', [
            $field, $this->getClac(), "(". join(',', $this->getValues()) .")"
        ]);
    }
}
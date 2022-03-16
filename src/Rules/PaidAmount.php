<?php

namespace Zw\Plugin\Ut\Rules;

use Zw\Plugin\Ut\Rule;
use Illuminate\Support\Str;
use Zw\Plugin\Ut\SqlBuilder;

/**
 * @rule paidAmount: 充值金额： 0 全部， 1 首充，2 三天内， 3 7天内， 4 14天内， 5 30天内
 */
class PaidAmount extends Rule
{
    /**
     * @var array|string[]
     */
    protected $validationRules = [
        'field' => 'require|eq:paidAmount', 'calc' => 'require|in:in,between', 'value' => 'require|array|min:1', 'type' => 'require|in:0,1,2,3,4,5'
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
        'money_sum_all', 'money_sum_first', 'money_sum_3', 'money_sum_7', 'money_sum_14', 'money_sum_30',
    ];

    /**
     * @param int $dbmsType
     * @return string
     */
    public function getSqlSegment(int $dbmsType = SqlBuilder::MySQL): string
    {
        $type = $this->getMeta('type');
        if (empty($this->typeMaps[$type])) {
            return '/** paidAmount not in types:0,1,2,3,4,5 */';
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
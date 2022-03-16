<?php

namespace Zw\Plugin\Ut\Rules;

use Zw\Plugin\Ut\Rule;
use Zw\Plugin\Ut\SqlBuilder;

/**
 * @rule paidAt: 充值时间(最近一次充值时间), hours: 小时, days: 天
 */
class PaidAt extends Rule
{
    /**
     * @var array|string[]
     */
    protected $validationRules = [
        'field' => 'require|eq:paidAt', 'calc' => 'require|in:between', 'value' => 'require|array|length:1,2', 'unit' => 'require|in:hour,day,-'
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
     * @param int $dbmsType
     * @return string
     */
    public function getSqlSegment(int $dbmsType = SqlBuilder::MySQL): string
    {
        $unit = $this->getMeta('unit');
        $field = $this->getField('last_order_time');
        // 日期范围内
        if ($unit == '-') {
            return $this->applyDateTimeRange($field);
        }

        // 时间单位范围内
        $layout = $this->getDateTimeLayoutFromUnit($unit);
        switch ($dbmsType) {
        case SqlBuilder::MySQL:
            $field = "TIMESTAMPDIFF({$unit}, DATE_FORMAT({$field}, '{$layout}'), DATE_FORMAT(NOW(), '{$layout}'))";
            break;
        case SqlBuilder::ClickHouse:
            $field = "dateDiff('{$unit}', date_trunc('{$unit}', {$field}), date_trunc('{$unit}', NOW()))";
            break;
        }

        [$a, $b] = $this->getValues();

        return join(' ', [$field, $this->getClac(), "{$a} AND {$b}"]);
    }
}
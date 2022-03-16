<?php

namespace Zw\Plugin\Ut\Rules;

use Zw\Plugin\Ut\Rule;
use Zw\Plugin\Ut\SqlBuilder;

/**
 * @rule paidStatus: 充值状态 0 未充值 1 已充值
 */
class PaidStatus extends Rule
{
    /**
     * @var array|string[]
     */
    protected $validationRules = [
        'field' => 'require|eq:paidStatus', 'calc' => 'require|in:in,notIn', 'value' => 'require|array|length:1,2'
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
        $field = $this->getField('money_sum_all');
        $values = $this->getValues();
        if (count($values) == 2) {
            return '/** paidStatus[money_sum_all]:0,1 */';
        }

        if ($this->getMeta('calc') == 'notIn') {
            return join(' ', [
                $field, $values[0] == 0 ? '>' : '<=', 0
            ]);
        }

        return join(' ', [
            $field, $values[0] == 0 ? '<=' : '>', 0
        ]);
    }
}
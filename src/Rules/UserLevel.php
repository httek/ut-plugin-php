<?php

namespace Zw\Plugin\Ut\Rules;

use Zw\Plugin\Ut\Rule;
use Zw\Plugin\Ut\SqlBuilder;

/**
 * @rule user level (vip_endtime): 用户状态 1 普通用户 2 vip用户
 * @note 因脚本提前处理，存在时间误差
 */
class UserLevel extends Rule
{
    /**
     * @var array|string[]
     */
    protected $validationRules = [
        'field' => 'require|eq:userLevel', 'calc' => 'require|in:in,notIn', 'value' => 'require|array|length:1,2'
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
        $values = array_values(array_unique($this->getValues()));
        $field = $this->getField('vip_endtime');
        if ($dbmsType == SqlBuilder::ClickHouse) {
            $field = "toDateTime({$field})";
        }

        if ($this->getMeta('calc') == 'notIn') {
            return join(' ', [
                $field, $values[0] == 1 ? '>' : '<', 'NOW()'
            ]);
        }

        if (count($values) == 1) {
            return join(' ', [
                $field, $values[0] == 1 ? '<' : '>=', 'NOW()'
            ]);
        }

        return '/** vip_endtime:1,2 */';
    }
}
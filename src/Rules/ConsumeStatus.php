<?php

namespace Zw\Plugin\Ut\Rules;

use Zw\Plugin\Ut\Rule;
use Zw\Plugin\Ut\SqlBuilder;

/**
 * @rule consumeStatus: 订阅情况：0 未订阅 1 普通订阅 2 VIP订阅
 */
class ConsumeStatus extends Rule
{
    /**
     * @var array|string[]
     */
    protected $validationRules = [
        'field' => 'require|eq:consumeStatus', 'calc' => 'require|in:in,notIn', 'value' => 'require|array|min:1,max:3'
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
        // 0 未订阅 1 普通订阅 2 VIP订阅
        $calc = $this->getMeta('calc');
        $values = array_unique($this->getValues());
        if ($calc == 'notIn') {
            switch (((int) $values[0])) {
            case 0:
                return join(' ', [
                    '(',
                    $this->getField('sub_kandian_all'), '>', 0, 'OR',
                    $this->getField('vip_sub'), '>', 0, ')'
                ]);
            case 1:
                return "{$this->getField('common_sub')} != 1";
            case 2:
                return "{$this->getField('vip_sub')} != 1";
            }
        }

        $segment = [];
        foreach ($values as $value) {
            switch (((int) $value)) {
            case 0:
                $segment[] = join(' ', [
                    '(',
                    $this->getField('sub_kandian_all'), '=', 0, 'AND',
                    $this->getField('vip_sub'), '=', 0, ')'
                ]); break;
            case 1:
                $segment[] = "{$this->getField('common_sub')} = 1"; break;
            case 2:
                $segment[] = "{$this->getField('vip_sub')} = 1"; break;
            }
        }

        return '(' . join(' OR ', $segment) . ')';
    }
}
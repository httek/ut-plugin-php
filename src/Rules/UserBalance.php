<?php

namespace Zw\Plugin\Ut\Rules;

use Zw\Plugin\Ut\Rule;
use Illuminate\Support\Str;
use Zw\Plugin\Ut\SqlBuilder;

/**
 * @rule userBalance: 用户余额： 0 全部， 1 永久书币， 2 免费书币
 */
class UserBalance extends Rule
{
    /**
     * @var array|string[]
     */
    protected $validationRules = [
        'field' => 'require|eq:userBalance', 'calc' => 'require|in:in,between', 'value' => 'require|array|min:1', 'type' => 'require|in:0,1,2'
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
        $type = (int) $this->getMeta('type');
        if ($type == 0) {
            $field = join('', [
                '(',
                $this->getField('kandian'),
                '+',
                $this->getField('free_kandian'),
                ')'
            ]);
        }

        else {
            $field = $this->getField($type == 1 ? 'kandian' : 'free_kandian');
        }

        //
        if (Str::contains("BETWEEN", $this->getClac())) {
            [$a, $b] = $this->getValues();

            return join(' ', [$field, $this->getClac(), "{$a} AND {$b}"]);
        }

        return join(' ', [
            $field, $this->getClac(), "(". join(',', $this->getValues()) .")"
        ]);
    }
}
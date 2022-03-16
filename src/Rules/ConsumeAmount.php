<?php

namespace Zw\Plugin\Ut\Rules;

use Zw\Plugin\Ut\Rule;
use Illuminate\Support\Str;
use Zw\Plugin\Ut\SqlBuilder;

/**
 * @rule consumeAmount: 订阅金额： 0 全部， 1 永久书币， 2 免费书币
 */
class ConsumeAmount extends Rule
{
    /**
     * @var array|string[]
     */
    protected $validationRules = [
        'field' => 'require|eq:consumeAmount', 'calc' => 'require|in:in,between', 'value' => 'require|array|min:1', 'type' => 'require|in:0,1,2'
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
        'sub_kandian_all', 'sub_kandian', 'sub_kandian_free',
    ];

    /**
     * @param int $dbmsType
     * @return string
     */
    public function getSqlSegment(int $dbmsType = SqlBuilder::MySQL): string
    {
        $type = $this->getMeta('type');
        if (empty($this->typeMaps[$type])) {
            return '/** ConsumeAmount not in types:0,1,2 */';
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
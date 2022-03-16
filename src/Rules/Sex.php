<?php

namespace Zw\Plugin\Ut\Rules;

use Zw\Plugin\Ut\Rule;
use Zw\Plugin\Ut\SqlBuilder;

/**
 * @rule sex: 性别
 */
class Sex extends Rule
{
    /**
     * @var array|string[]
     */
    protected $validationRules = [
        'field' => 'require|eq:sex', 'calc' => 'require|in:in,notIn', 'value' => 'require|array|length:1,3'
    ];

    /**
     * @var array|string[]
     */
    protected $valueMaps = ['未知', '男', '女'];

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
        return join(' ', [
            $this->getField(), $this->getClac(), "(". join(',', $this->getValues()) .")"
        ]);
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        $values = array_intersect_key(
            $this->valueMaps, array_flip($this->getMeta('value'))
        );

        return array_map(function ($item) { return "'{$item}'"; }, $values);
    }
}
<?php

namespace Zw\Plugin\Ut\Rules;

use Zw\Plugin\Ut\Rule;
use Illuminate\Support\Str;
use Zw\Plugin\Ut\SqlBuilder;

/**
 * @rule sys: 系统
 */
class Os extends Rule
{
    /**
     * @var array|string[]
     */
    protected $validationRules = [
        'field' => 'require|eq:os', 'calc' => 'require|in:in,notIn', 'value' => 'require|array|length:1,2'
    ];

    /**
     * @var array|string[]
     */
    protected $valueMaps = ['未知', 'iOS', 'Android'];

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
        $field = $this->getField('sys');
        if (Str::contains("BETWEEN", $this->getClac())) {
            [$a, $b] = $this->getValues();

            return join(' ', [$field, $this->getClac(), "{$a} AND {$b}"]);
        }

        return join(' ', [
            $field, $this->getClac(), "(". join(',', $this->getValues()) .")"
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
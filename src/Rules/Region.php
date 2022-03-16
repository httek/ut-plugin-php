<?php

namespace Zw\Plugin\Ut\Rules;

use Zw\Plugin\Ut\Rule;
use Zw\Plugin\Ut\SqlBuilder;

/**
 * @rule region: 省，市
 */
class Region extends Rule
{
    /**
     * @var array|string[]
     */
    protected $validationRules = [
        'field' => 'require|eq:region', 'calc' => 'require|in:in', 'value' => 'require|array|length:1,2'
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
        $values = $this->getValues();
        $segment  = join(' ', [
            $this->getField('province'), "LIKE", "'{$values[0]}%'"
        ]);

        if (isset($values[1])) {
            $segment .= ' AND ' . join(' ', [
                $this->getField('city'), "LIKE", "'{$values[1]}%'"
            ]);
        }

        return $segment;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return array_map(function ($item) {
            return static::rTrimChars($item);
        }, $this->getMeta('value'));
    }
}
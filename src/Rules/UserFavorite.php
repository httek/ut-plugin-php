<?php

namespace Zw\Plugin\Ut\Rules;

use Zw\Plugin\Ut\Rule;
use Zw\Plugin\Ut\SqlBuilder;

/**
 * @rule user favorite : 用户男女频喜爱度
 *
 * 1男频: 1 --> 2 - PCT
 * 2女频: 1 + PCT --> 2
 */
class UserFavorite extends Rule
{
    /**
     * @var array|string[]
     */
    protected $validationRules = [
        'field' => 'require|eq:userFavorite', 'type' => 'in:1,2', 'calc' => 'require|in:between', 'value' => 'require|array|length:2'
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
        $values = array_values($this->getValues());
        foreach ($values as $index => $value) {
            if ($value < 1 || $value > 100) {
                return "/** invalid read_favorite values.{$index}: {$value} */";
            }
        }

        $left = $right = 0;
        switch (((int) ($this->getMeta()['type'] ?? 0)))
        {
            case 1:
                $right = round(2 - ($values[0] / 100),2);
                $left = round(2 - ($values[1] / 100),2);
                break;
            case 2:
                $left = round(1 + ($values[0] / 100), 2);
                $right = round(1 + ($values[1] / 100), 2);
                break;
        }

        if ($left == 0 || $right == 0) {
            return "/** invalid read_favorite values: {$left} - {$right} */";
        }

        if ($dbmsType == SqlBuilder::ClickHouse) {
            $left = "'{$left}'"; $right = "'{$right}'";
        }

        return join(' ', [$this->getField('read_favorite'), 'BETWEEN', $left, 'AND', $right]);
    }
}
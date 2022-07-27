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
        'field' => 'require|eq:userFavorite', 'type' => 'in:1,2', 'calc' => 'require|in:eq,lt,gt', 'value' => 'require|array|length:1'
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
        $value = array_values(array_unique($this->getValues()))[0] ?? 0;
        $field = $this->getField('read_favorite');
        $type = $this->getMeta()['type'] ?? 0;

        if ($value <= 0 || $value > 100) {
            return "/** invalid read_favorite value: {$value} */";
        }

        $percent = round($value / 100, 2);
        $value = $type == 1 ? (2 - $percent) : (1 + $percent);
        if ($dbmsType == SqlBuilder::ClickHouse) {
            $value = "'{$value}'";
        }

        switch ($this->getClac()) {
            case 'lt':
                $op = $type == 1 ? '>' : '<';
                return join(' ', [$field, $op, $value]);
            case 'gt':
                $op = $type == 1 ? '<' : '>';
                return join(' ', [$field, $op, $value]);
            case 'eq':
                return join(' ', [$field, '=', $value]);
        }

        return '';
    }

    /**
     * @return string
     */
    public function getClac(): string
    {
        return $this->getMeta()['calc'] ?? '';
    }
}
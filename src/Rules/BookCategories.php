<?php

namespace Zw\Plugin\Ut\Rules;

use Zw\Plugin\Ut\Rule;
use Zw\Plugin\Ut\SqlBuilder;

/**
 * @rule bookCategories: 书籍分类: [1,2,3,4]
 */
class BookCategories extends Rule
{
    /**
     * @var array|string[]
     */
    protected $validationRules = [
        'field' => 'require|eq:bookCategories', 'calc' => 'require|in:in,notIn', 'value' => 'require|array|min:1'
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
        $field = $this->getField('book_category_ids');
        if (! count($this->getValues())) {
            return '/** bookCategories with empty items */';
        }

        $segment = null;
        $eq = $this->getMeta('calc') == 'in' ? 1 : 0;
        switch ($dbmsType) {
        case SqlBuilder::MySQL:
            $values = implode('|', $this->getValues());
            $segment = "(REPLACE({$field}, ',', '|') REGEXP '{$values}') = {$eq}";

            break;
        case SqlBuilder::ClickHouse:
            $values = implode(',', static::wrapQuota($this->getValues()));
            $segment = "({$field} != [] AND hasAny({$field}, [{$values}]) = {$eq})";

            break;
        }

        return join(' ', array_filter([$segment]));
    }
}
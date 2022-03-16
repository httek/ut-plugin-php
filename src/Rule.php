<?php

namespace Zw\Plugin\Ut;

use think\Validate;

abstract class Rule implements RuleInterface
{

    /**
     * @var array|string[]
     */
    protected $ruleNames = [
        'sex' => '性别',
        'device' => '设备',
        'region' => '地域',
        'subscribe' => '关注状态',
        'subscribeAt' => '关注时间',
        'registeredAt' => '注册时间',
        'userLevel' => '用户状态',
        'userBalance' => '用户余额',
        'paidStatus' => '充值状态',
        'paidTotal' => '充值次数',
        'paidAmount' => '充值金额',
        'paidAt' => '充值时间',
        'paidBooks' => '充值书籍',
        'consumeStatus' => '订阅情况',
        'consumeAmount' => '订阅金额',
        'consumeBooks' => '订阅书籍',
        'activateAt' => '活跃时间',
        'interactAt' => '交互时间',
        'bookCategories' => '书籍分类',
        'userReadHistories' => '阅读记录',
    ];

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @var string
     */
    protected $tableAlias = 'uf';

    /**
     * @var string
     */
    protected $validationMessage = '';

    /**
     * @param string $name
     * @return array|string
     */
    public function getMeta(string $name = '')
    {
        if ($name) {
            if (! isset($this->meta[$name])) {
                throw new \RuntimeException(
                    __METHOD__ . " => Meta: {$name} not exists."
                );
            }

            return $this->meta[$name];
        }

        return $this->meta;
    }

    /**
     * @return string
     */
    public function getValidationMessage(): string
    {
        if ($this->validationMessage) {
            return sprintf("%s :=> %s",
                $this->ruleNames[$this->getMeta('field')] ?? $this->getMeta('field'),
                "数据格式有误. ({$this->validationMessage})");
        }

        return '';
    }

    /**
     * @return array
     */
    public function getValidationRules(): array
    {
        return property_exists(static::class, 'validationRules')
            ? $this->validationRules : [];
    }

    /**
     * @param string $tableAlias
     * @return Rule
     */
    public function setTableAlias(string $tableAlias): self
    {
        $this->tableAlias = $tableAlias;

        return $this;
    }

    /**
     * @return string
     */
    public function getTableAlias(): string
    {
        return $this->tableAlias;
    }

    /**
     * @param array $rules
     * @param array $messages
     * @return bool
     */
    public function valid(array $rules = [], array $messages = []): bool
    {
        $rules = $rules ?: $this->getValidationRules();
        $valid = new Validate();
        $valid->rule($rules)->message($messages);

        if (! ($status = $valid->check($this->getMeta()))) {
            $this->validationMessage = $valid->getError();
        }

        return $status;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->getMeta('value') ?? [];
    }

    /**
     * @param string $name
     * @param string $tablePrefix
     * @return string
     */
    public function getField(string $name = '', string $tablePrefix = ''): string
    {
        $filed = $name ?: $this->getMeta()['field'];
        $tablePrefix = $tablePrefix ?: $this->getTableAlias();

        return "`{$tablePrefix}`.`{$filed}`";
    }

    /**
     * @return string
     */
    public function getClac(): string
    {
        if (! $clac = $this->getMeta()['calc'] ?? '') {
            return '';
        }

        if (in_array($clac, ['in', 'between'])) {
            return strtoupper($clac);
        }

        return 'NOT IN';
    }

    /**
     * @param string $field
     * @param array $values
     * @return string
     */
    public function applyDateTimeRange(string $field = '', array $values = []): string
    {
        [$a, $b] = $values ?: $this->getValues();
        [$a, $b] = [date('Y-m-d 00:00:00', strtotime($a)), date('Y-m-d 23:59:59', strtotime($b))];

        return join(' ', [$field ?: $this->getField(), $this->getClac(), "'{$a}' AND '{$b}'"]);
    }

    /**
     * @param string $unit
     * @return string
     */
    public function getDateTimeLayoutFromUnit(string $unit): string
    {
        $layout = '%Y-%m-%d';
        if ($unit == 'hour') {
            $layout .= ' %H';
        }

        return $layout;
    }

    /**
     * Trim chars
     *
     * @param string $content
     * @param array $chars
     * @return string
     */
    protected static function rTrimChars(string $content, array $chars = ['省', '市', '区', '县']) : string {
        foreach ($chars as $char)
            if (mb_strpos($content, $char))
                $content = mb_substr($content, 0, mb_strpos($content, $char), 'utf-8');

        return $content;
    }

    /**
     * @param array $items
     * @return array
     */
    protected static function wrapQuota(array $items): array
    {
        return array_map(function ($item) {return "'{$item}'";}, $items);
    }
}
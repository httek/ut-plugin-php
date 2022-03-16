<?php

namespace Zw\Plugin\Ut;

use Illuminate\Support\Str;

/**
 * @package UserTag V2
 */
class SqlBuilder
{
    const
        MySQL = 1,
        ClickHouse = 2;

    /**
     * @var RuleInterface[]
     */
    protected $rules = [];

    /**
     * @var string
     */
    private $prefix = 'SELECT `uf`.`user_id` AS uid, `uf`.`channel_id` AS cid, `uf`.`openid` AS oid, `uf`.`nickname` AS name FROM user_info AS uf';

    /**
     * @var string
     */
    private $extraSql = '';

    /**
     * @var int
     */
    private $dbms = self::MySQL;

    /**
     * @return int
     */
    public function getDbms(): int
    {
        return $this->dbms;
    }

    /**
     * @return bool
     */
    public function isCkMode(): bool
    {
        return $this->getDbms() == static::ClickHouse;
    }

    /**
     * @return bool
     */
    public function isMySQLMode(): bool
    {
        return $this->getDbms() == static::MySQL;
    }

    /**
     * @param int $dbms
     * @return SqlBuilder
     */
    public function setDbms(int $dbms)
    {
        if (! in_array($dbms, [static::MySQL, static::ClickHouse])) {
            throw new \RuntimeException("Unsupported dbms flag: {$dbms}");
        }

        $this->dbms = $dbms;

        return $this;
    }

    /**
     * @return string
     */
    public function getExtraSql(): string
    {
        return $this->extraSql;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     * @return SqlBuilder
     */
    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * @param RuleInterface[] $rules
     * @return SqlBuilder
     */
    public function setRules(array $rules)
    {
        foreach ($rules as $rule) {
            if (! $rule->valid()) {
                throw new \RuntimeException(
                    sprintf("%s => %s => %s", __METHOD__,
                        get_class($rule), $rule->getValidationMessage())
                );
            }
        }

        $this->rules = array_merge($this->rules, ...$rules);

        return $this;
    }

    /**
     * @return RuleInterface[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * @param \Zw\Plugin\Ut\Rule $rule
     * @param bool $valid
     * @return $this
     */
    public function addRule(Rule $rule, bool $valid = true)
    {
        if ($valid && ! $rule->valid()) {
            throw new \RuntimeException(
                sprintf("%s => %s => %s", __METHOD__,
                    get_class($rule), $rule->getValidationMessage())
            );
        }

        $this->rules[] = $rule;

        return $this;
    }

    /**
     * @param RuleInterface[] $rules
     * @return string
     */
    public function toSql(array $rules = []): string
    {
        $sqlSegments = '';
        $this->rules = array_merge($this->rules, ...$rules);
        foreach ($this->rules as $rule) {
            if (! $segment = $rule->getSqlSegment($this->getDbms())) {
                continue;
            }

            if (Str::startsWith($segment, '/**')) {
                $sqlSegments .= " {$segment} "; continue;
            }

            $sqlSegments .= join(" ", [
                $sqlSegments ? ' AND' : 'WHERE', "{$segment}"
            ]);
        }

        if (! $sqlSegments) {
            return '/** Empty sql. */';
        }

        $prefix = $this->getPrefix();
        if ($this->isCkMode()) {
            $prefix = 'SELECT `uf`.`user_id` AS uid, `uf`.`channel_id` AS cid, `uf`.`openid` AS oid, `uf`.`nickname` AS name FROM user_info_hot AS uf';
        }

        return join(' ', array_filter([
                $prefix, $this->isCkMode() ? 'FINAL' : null, trim($sqlSegments, "AND")
            ])) . $this->getExtraSql();
    }

    /**
     * @param string $field
     * @return string
     */
    public function getClassFromField(string $field): string
    {
        $ruleClassWithNamespace = "\\Zw\\Plugin\\Ut\\Rules\\" . ucfirst($field);
        if (! class_exists($ruleClassWithNamespace)) {
            throw new \RuntimeException('Rule class not exists: ' . $ruleClassWithNamespace);
        }

        return $ruleClassWithNamespace;
    }

    /**
     * @param int $operateRange
     * @param string $filed
     * @return $this
     */
    public function withOperateTimeLimited(int $operateRange = 0, string $filed = '`uf`.`operate_time`')
    {
        $coveredTime = $operateRange ?: 3600 * 50;
        $segment = " AND (UNIX_TIMESTAMP() - UNIX_TIMESTAMP({$filed})) <= {$coveredTime})";
        if ($this->isCkMode()) {
            $segment = " AND toUnixTimestamp(now()) - toUnixTimestamp({$filed}) <= {$coveredTime}";
        }

        $this->extraSql .= $segment;

        return $this;
    }

    /**
     * @param int $id
     * @param string $filed
     * @return $this
     */
    public function withUserId(int $id = 0, string $filed = '`uf`.`user_id`')
    {
        $segment = " AND ({$filed} = {$id})";
        if ($this->isCkMode()) {
            $segment = " AND ({$filed} = '{$id}')";
        }

        $this->extraSql .= $segment;

        return $this;
    }

    /**
     * @param int $id
     * @param string $filed
     * @return $this
     */
    public function withChannelId(int $id = 0, string $filed = '`uf`.`channel_id`')
    {
        $segment = " AND ({$filed} = {$id})";
        if ($this->isCkMode()) {
            $segment = " AND ({$filed} = '{$id}')";
        }

        $this->extraSql .= $segment;

        return $this;
    }

    /**
     * @return $this
     */
    public function setMySqlMode()
    {
        return $this->setDbms(static::MySQL);
    }

    /**
     * @return $this
     */
    public function setClickHouseMode()
    {
        return $this->setDbms(static::ClickHouse);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toSql();
    }
}
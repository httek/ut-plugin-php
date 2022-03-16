<?php

namespace Zw\Plugin\Ut;

interface RuleInterface
{
    /**
     * @param int $dbmsType
     * @return string
     */
    public function getSqlSegment(int $dbmsType = SqlBuilder::MySQL): string;

    /**
     * @param array $rules
     * @param array $messages
     * @return bool
     */
    public function valid(array $rules = [], array $messages = []): bool;

    /**
     * @return string
     */
    public function getValidationMessage(): string;
}
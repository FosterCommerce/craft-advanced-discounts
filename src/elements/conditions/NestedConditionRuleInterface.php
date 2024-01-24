<?php

namespace fostercommerce\coupons\elements\conditions;

use craft\base\conditions\ConditionRuleInterface;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\conditions\ElementConditionRuleInterface;

interface NestedConditionRuleInterface extends ElementConditionRuleInterface
{
    public function getNestedCondition(): ElementConditionInterface;
}

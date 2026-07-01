<?php

namespace fostercommerce\advanceddiscounts\elements\conditions;

use craft\elements\conditions\ElementConditionInterface;
use craft\elements\conditions\ElementConditionRuleInterface;

interface NestedConditionRuleInterface extends ElementConditionRuleInterface
{
	public function getNestedCondition(): ElementConditionInterface;
}

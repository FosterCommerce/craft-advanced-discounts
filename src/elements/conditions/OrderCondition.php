<?php

namespace fostercommerce\coupons\elements\conditions;

use craft\commerce\elements\conditions\orders\ItemSubtotalConditionRule;
use craft\commerce\elements\conditions\orders\ItemTotalConditionRule;
use craft\commerce\elements\conditions\orders\TotalConditionRule;
use craft\commerce\elements\conditions\orders\TotalPriceConditionRule;
use craft\commerce\elements\conditions\orders\TotalQtyConditionRule;
use craft\elements\conditions\ElementCondition;

class OrderCondition extends ElementCondition
{
	public ?string $addRuleLabel = 'OR';

	protected function conditionRuleTypes(): array
	{
		return array_merge([
			ItemSubtotalConditionRule::class,
			ItemTotalConditionRule::class,
			TotalPriceConditionRule::class,
			TotalQtyConditionRule::class,
			TotalConditionRule::class,
		]);
	}
}

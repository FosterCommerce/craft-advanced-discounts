<?php

namespace fostercommerce\coupons\elements\conditions;

use craft\elements\conditions\ElementCondition;

class OrderActionCondition extends ElementCondition
{
	public ?string $addRuleLabel = 'Add condition';

	protected function conditionRuleTypes(): array
	{
		return array_merge([
			HasPurchasableConditionRule::class,
			RelatedToConditionRule::class,
		]);
	}
}

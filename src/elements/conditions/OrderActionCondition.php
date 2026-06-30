<?php

namespace fostercommerce\advancedDiscounts\elements\conditions;

use craft\elements\conditions\ElementCondition;

class OrderActionCondition extends ElementCondition
{
	public ?string $addRuleLabel = 'Add condition';

	/**
	 * @return array<int, class-string>
	 */
	protected function selectableConditionRules(): array
	{
		return array_merge([
			HasPurchasableConditionRule::class,
			RelatedToConditionRule::class,
		]);
	}
}

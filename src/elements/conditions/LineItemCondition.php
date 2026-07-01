<?php

namespace fostercommerce\advanceddiscounts\elements\conditions;

use craft\elements\conditions\ElementCondition;

class LineItemCondition extends ElementCondition
{
	public ?string $addRuleLabel = 'Add condition';

	/**
	 * @return array<int, class-string>
	 */
	protected function selectableConditionRules(): array
	{
		return [
			SpecificPurchasableConditionRule::class,
			RelatedToConditionRule::class,
		];
	}
}

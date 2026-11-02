<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\elements\conditions;

use craft\commerce\elements\conditions\variants\VariantCondition;

class DiscountVariantCondition extends VariantCondition
{
	protected function selectableConditionRules(): array
	{
		return array_merge(parent::selectableConditionRules(), [
			SelectedElementsConditionRule::class,
		]);
	}
}

<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\elements\conditions;

use craft\commerce\elements\conditions\products\ProductCondition;

class DiscountProductCondition extends ProductCondition
{
	protected function selectableConditionRules(): array
	{
		return array_merge(parent::selectableConditionRules(), [
			SelectedElementsConditionRule::class,
		]);
	}
}

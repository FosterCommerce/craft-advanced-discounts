<?php

namespace fostercommerce\advanceddiscounts\elements\conditions;

use craft\elements\conditions\ElementCondition;

class BundleCondition extends ElementCondition
{
	/**
	 * @return array<int, class-string>
	 */
	protected function selectableConditionRules(): array
	{
		return [
			BogoCartActionRule::class,
		];
	}
}

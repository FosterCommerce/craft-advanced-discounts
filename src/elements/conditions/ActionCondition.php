<?php

namespace fostercommerce\advancedDiscounts\elements\conditions;

use Craft;
use craft\elements\conditions\ElementCondition;

class ActionCondition extends ElementCondition
{
	public function init(): void
	{
		$this->addRuleLabel = Craft::t('advanced-discounts', 'Add an action');
		parent::init();
	}

	/**
	 * @return array<int, class-string>
	 */
	protected function selectableConditionRules(): array
	{
		return array_merge([
			OrderActionRule::class,
			LineItemActionRule::class,
			ShippingMethodActionRule::class,
			MessageActionRule::class,
		]);
	}
}

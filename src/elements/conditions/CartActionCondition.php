<?php

namespace fostercommerce\advanceddiscounts\elements\conditions;

use Craft;
use craft\elements\conditions\ElementCondition;

class CartActionCondition extends ElementCondition
{
	public function init(): void
	{
		$this->addRuleLabel = Craft::t('advanced-discounts', 'Add a cart action');
		parent::init();
	}

	/**
	 * @return array<int, class-string>
	 */
	protected function selectableConditionRules(): array
	{
		return array_merge([
			OrderCartActionRule::class,
			LineItemCartActionRule::class,
			ShippingMethodCartActionRule::class,
		]);
	}
}

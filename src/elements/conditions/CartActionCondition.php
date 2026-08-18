<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\elements\conditions;

use Craft;
use craft\elements\conditions\ElementCondition;

class CartActionCondition extends ElementCondition
{
	public function init(): void
	{
		$this->addRuleLabel = Craft::t('advanced-discounts', 'conditions.addCartAction');
		parent::init();
	}

	/**
	 * @return array<int, class-string>
	 */
	protected function selectableConditionRules(): array
	{
		return [
			OrderCartActionRule::class,
			LineItemCartActionRule::class,
			ShippingMethodCartActionRule::class,
		];
	}
}

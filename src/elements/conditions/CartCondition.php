<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\elements\conditions;

use Craft;
use craft\elements\conditions\ElementCondition;

class CartCondition extends ElementCondition
{
	public function init(): void
	{
		$this->addRuleLabel = Craft::t('advanced-discounts', 'conditions.addCartCondition');
		parent::init();
	}

	/**
	 * @return array<int, class-string>
	 */
	protected function selectableConditionRules(): array
	{
		return [
			LineItemConditionRule::class,
			OrderConditionRule::class,
			UserConditionRule::class,
			DateRangeConditionRule::class,
		];
	}
}

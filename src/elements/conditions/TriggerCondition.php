<?php

namespace fostercommerce\advanceddiscounts\elements\conditions;

use Craft;
use craft\elements\conditions\ElementCondition;

class TriggerCondition extends ElementCondition
{
	public function init(): void
	{
		$this->addRuleLabel = Craft::t('advanced-discounts', 'OR');
		parent::init();
	}

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

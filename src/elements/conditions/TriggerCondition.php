<?php

namespace fostercommerce\coupons\elements\conditions;

use Craft;
use craft\elements\conditions\ElementCondition;

class TriggerCondition extends ElementCondition
{
	public function init(): void
	{
		$this->addRuleLabel = Craft::t('coupons', 'OR');
		parent::init();
	}

	/**
	 * @return array<int, class-string>
	 */
	protected function conditionRuleTypes(): array
	{
		return array_merge([
			HasPurchasableConditionRule::class,
			RelatedToConditionRule::class,
		]);
	}
}

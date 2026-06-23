<?php

namespace fostercommerce\coupons\elements\conditions;

use Craft;
use craft\elements\conditions\ElementCondition;

class ActionCondition extends ElementCondition
{
	public function init(): void
	{
		$this->addRuleLabel = Craft::t('coupons', 'Add an action');
		parent::init();
	}

	/**
	 * @return array<int, class-string>
	 */
	protected function conditionRuleTypes(): array
	{
		return array_merge([
			OrderActionRule::class,
			ShippingMethodActionRule::class,
		]);
	}
}

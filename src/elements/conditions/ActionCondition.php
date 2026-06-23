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

	protected function conditionRuleTypes(): array
	{
		return array_merge([
			OrderActionRule::class,
			ShippingMethodActionRule::class,
		]);
	}
}

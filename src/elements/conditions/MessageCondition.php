<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\elements\conditions;

use Craft;
use craft\elements\conditions\ElementCondition;

class MessageCondition extends ElementCondition
{
	/**
	 * Whether this condition belongs to a Buy X, Get Y discount panel, which determines
	 * which message placeholders are applicable in {@see MessageActionRule}.
	 */
	public bool $bundle = false;

	public function init(): void
	{
		$this->addRuleLabel = Craft::t('advanced-discounts', 'conditions.addMessage');
		parent::init();
	}

	/**
	 * @return array<int, class-string>
	 */
	protected function selectableConditionRules(): array
	{
		return [
			MessageActionRule::class,
		];
	}
}

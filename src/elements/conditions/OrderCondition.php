<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\elements\conditions;

use Craft;
use craft\base\ElementInterface;
use craft\commerce\elements\conditions\orders\ItemSubtotalConditionRule;
use craft\commerce\elements\conditions\orders\ItemTotalConditionRule;
use craft\commerce\elements\conditions\orders\TotalConditionRule;
use craft\commerce\elements\conditions\orders\TotalPriceConditionRule;
use craft\commerce\elements\conditions\orders\TotalQtyConditionRule;
use craft\elements\conditions\ElementCondition;
use craft\elements\conditions\ElementConditionRuleInterface;

class OrderCondition extends ElementCondition
{
	public function init(): void
	{
		$this->addRuleLabel = Craft::t('advanced-discounts', 'conditions.or');
		parent::init();
	}

	public function matchElement(ElementInterface $element): bool
	{
		$conditionRules = $this->getConditionRules();
		if ($conditionRules === []) {
			return true;
		}

		foreach ($conditionRules as $conditionRule) {
			/** @var ElementConditionRuleInterface $conditionRule */
			if ($conditionRule->matchElement($element)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return array<int, class-string>
	 */
	protected function selectableConditionRules(): array
	{
		return [
			ItemSubtotalConditionRule::class,
			ItemTotalConditionRule::class,
			TotalPriceConditionRule::class,
			TotalQtyConditionRule::class,
			TotalConditionRule::class,
		];
	}
}

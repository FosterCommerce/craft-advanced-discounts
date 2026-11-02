<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\elements\conditions;

use Craft;
use craft\base\ElementInterface;
use craft\elements\conditions\ElementCondition;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\helpers\Json;

/**
 * Matches a line item's variant when any row matches.
 */
class GlobalProductCondition extends ElementCondition
{
	public function getBuilderInnerHtml(bool $autofocusAddButton = false): string
	{
		// Label the add button OR once a row exists, since rows match when any one matches
		$this->addRuleLabel = $this->getConditionRules() === []
			? Craft::t('advanced-discounts', 'conditions.limitTo')
			: Craft::t('advanced-discounts', 'conditions.or');

		// Pass the translated OR to the stylesheet, which labels rows after the first
		Craft::$app->getView()->registerCss(':root { --advanced-discount-or-label: ' . Json::encode(Craft::t('advanced-discounts', 'conditions.or')) . '; }');

		return parent::getBuilderInnerHtml($autofocusAddButton);
	}

	public function matchElement(ElementInterface $element): bool
	{
		foreach ($this->getConditionRules() as $conditionRule) {
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
			ProductsConditionRule::class,
			VariantsConditionRule::class,
		];
	}
}

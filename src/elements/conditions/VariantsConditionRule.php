<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\elements\conditions;

use Craft;
use craft\base\ElementInterface;
use craft\commerce\elements\Variant;

class VariantsConditionRule extends NestedElementConditionRule
{
	public function getLabel(): string
	{
		return Craft::t('advanced-discounts', 'conditions.limitTo.variants');
	}

	protected function conditionClass(): string
	{
		return DiscountVariantCondition::class;
	}

	protected function elementToMatch(Variant $variant): ?ElementInterface
	{
		return $variant;
	}
}

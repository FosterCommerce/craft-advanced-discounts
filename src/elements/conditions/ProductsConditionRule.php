<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\elements\conditions;

use Craft;
use craft\base\ElementInterface;
use craft\commerce\elements\Variant;

class ProductsConditionRule extends NestedElementConditionRule
{
	public function getLabel(): string
	{
		return Craft::t('advanced-discounts', 'conditions.limitTo.products');
	}

	protected function conditionClass(): string
	{
		return DiscountProductCondition::class;
	}

	protected function elementToMatch(Variant $variant): ?ElementInterface
	{
		return $variant->getOwner();
	}
}

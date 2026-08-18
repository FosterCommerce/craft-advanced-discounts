<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\discounttypes;

use Craft;
use fostercommerce\advanceddiscounts\base\DiscountType;
use fostercommerce\advanceddiscounts\elements\conditions\BundleCondition;

class BuyXGetYDiscountType extends DiscountType
{
	public static function handle(): string
	{
		return 'buyXGetY';
	}

	public static function displayName(): string
	{
		return Craft::t('advanced-discounts', 'discountType.buyXGetY');
	}

	public static function actionConditionClass(): string
	{
		return BundleCondition::class;
	}

	public static function actionLabel(): string
	{
		return Craft::t('advanced-discounts', 'discountType.buyXGetY.actionLabel');
	}

	public static function actionInstructions(): string
	{
		return Craft::t('advanced-discounts', 'discountType.buyXGetY.actionInstructions');
	}
}

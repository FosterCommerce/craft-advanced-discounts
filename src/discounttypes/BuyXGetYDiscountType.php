<?php

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
		return Craft::t('advanced-discounts', 'Buy X, Get Y');
	}

	public static function actionConditionClass(): string
	{
		return BundleCondition::class;
	}

	public static function actionLabel(): string
	{
		return Craft::t('advanced-discounts', 'Buy X, Get Y');
	}

	public static function actionInstructions(): string
	{
		return Craft::t('advanced-discounts', 'Choose the items the customer buys and the items they get.');
	}
}

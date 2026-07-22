<?php

namespace fostercommerce\advanceddiscounts\discounttypes;

use Craft;
use fostercommerce\advanceddiscounts\base\DiscountType;
use fostercommerce\advanceddiscounts\elements\conditions\CartActionCondition;

class AdvancedDiscountType extends DiscountType
{
	public static function handle(): string
	{
		return 'advanced';
	}

	public static function displayName(): string
	{
		return Craft::t('advanced-discounts', 'Advanced');
	}

	public static function actionConditionClass(): string
	{
		return CartActionCondition::class;
	}

	public static function actionLabel(): string
	{
		return Craft::t('advanced-discounts', 'Cart Actions');
	}

	public static function actionInstructions(): string
	{
		return Craft::t('advanced-discounts', 'Applied when the customer matches the rules above.');
	}
}

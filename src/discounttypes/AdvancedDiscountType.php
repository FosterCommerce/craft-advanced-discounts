<?php

declare(strict_types=1);

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
		return Craft::t('advanced-discounts', 'discountType.advanced');
	}

	public static function actionConditionClass(): string
	{
		return CartActionCondition::class;
	}

	public static function actionLabel(): string
	{
		return Craft::t('advanced-discounts', 'discountType.advanced.actionLabel');
	}

	public static function actionInstructions(): string
	{
		return Craft::t('advanced-discounts', 'discountType.advanced.actionInstructions');
	}
}

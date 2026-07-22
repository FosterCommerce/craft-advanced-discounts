<?php

namespace fostercommerce\advanceddiscounts\base;

use craft\commerce\elements\Order;
use craft\commerce\models\OrderAdjustment;
use fostercommerce\advanceddiscounts\models\Discount;

interface DiscountTypeInterface
{
	public static function handle(): string;

	public static function displayName(): string;

	/**
	 * @return class-string<\craft\elements\conditions\ElementConditionInterface>
	 */
	public static function actionConditionClass(): string;

	public static function actionLabel(): string;

	public static function actionInstructions(): string;

	public function getSettingsHtml(Discount $discount): string;

	/**
	 * @return OrderAdjustment[]
	 */
	public function getAdjustments(Order $order, Discount $discount): array;

	/**
	 * @return string[]
	 */
	public function getMessages(Order $order, Discount $discount): array;
}

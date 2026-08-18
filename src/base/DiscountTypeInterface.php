<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\base;

use craft\commerce\elements\Order;
use craft\commerce\models\OrderAdjustment;
use craft\elements\conditions\ElementConditionInterface;
use fostercommerce\advanceddiscounts\models\Discount;

interface DiscountTypeInterface
{
	public static function handle(): string;

	public static function displayName(): string;

	/**
	 * @return class-string<ElementConditionInterface>
	 */
	public static function actionConditionClass(): string;

	public static function actionLabel(): string;

	public static function actionInstructions(): string;

	/**
	 * @return array<string, string>
	 */
	public static function messagePlaceholders(): array;

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

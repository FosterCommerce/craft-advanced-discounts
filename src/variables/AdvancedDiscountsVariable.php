<?php

namespace fostercommerce\advancedDiscounts\variables;

use Craft;
use craft\commerce\elements\conditions\orders\ItemSubtotalConditionRule;
use craft\commerce\elements\conditions\orders\ItemTotalConditionRule;
use craft\commerce\elements\conditions\orders\TotalConditionRule;
use craft\commerce\elements\conditions\orders\TotalPriceConditionRule;
use craft\commerce\elements\Order;
use fostercommerce\advancedDiscounts\elements\conditions\LineItemActionRule;
use fostercommerce\advancedDiscounts\elements\conditions\MessageActionRule;
use fostercommerce\advancedDiscounts\elements\conditions\OrderActionRule;
use fostercommerce\advancedDiscounts\elements\conditions\OrderConditionRule;
use fostercommerce\advancedDiscounts\enums\DiscountType;
use fostercommerce\advancedDiscounts\models\Discount;
use fostercommerce\advancedDiscounts\Plugin;

class AdvancedDiscountsVariable
{
	/**
	 * Returns messages from all applicable discounts for the given order,
	 * with placeholders resolved against the order and discount rules.
	 *
	 * Supported placeholders:
	 *   {discountAmount}   — the discount value (currency or percentage)
	 *   {amountRemaining}  — how much more the customer needs to spend to qualify
	 *
	 * @return string[]
	 */
	public function getMessages(Order $order): array
	{
		$messages = [];

		foreach (Plugin::getInstance()->coupons->getAllCoupons() as $coupon) {
			if (! $coupon->enabled) {
				continue;
			}

			if ($coupon->code !== null && strcasecmp($coupon->code, $order->couponCode ?? '') !== 0) {
				continue;
			}

			if (! $coupon->getTriggerCondition()->matchElement($order)) {
				continue;
			}

			foreach ($coupon->getActionCondition()->getConditionRules() as $rule) {
				if ($rule instanceof MessageActionRule && $rule->message !== '') {
					$messages[] = $this->resolvePlaceholders($rule->message, $coupon, $order);
				}
			}
		}

		return $messages;
	}

	/**
	 * Returns the first applicable message for the given order, or null if none.
	 */
	public function getMessage(Order $order): ?string
	{
		return $this->getMessages($order)[0] ?? null;
	}

	private function resolvePlaceholders(string $message, Discount $coupon, Order $order): string
	{
		$placeholders = [];

		// {discountAmount} — value from the first action rule that has one
		foreach ($coupon->getActionCondition()->getConditionRules() as $rule) {
			if (($rule instanceof OrderActionRule || $rule instanceof LineItemActionRule) && $rule->discountValue !== null) {
				$placeholders['{discountAmount}'] = $rule->discountType === DiscountType::Percentage
					? $rule->discountValue . '%'
					: Craft::$app->getFormatter()->asCurrency($rule->discountValue, $order->paymentCurrency);
				break;
			}
		}

		// {amountRemaining} — how much more the customer needs to spend
		$amountRemaining = $this->computeAmountRemaining($coupon, $order);
		if ($amountRemaining !== null) {
			$placeholders['{amountRemaining}'] = Craft::$app->getFormatter()->asCurrency($amountRemaining, $order->paymentCurrency);
		}

		return strtr($message, $placeholders);
	}

	/**
	 * Walks the trigger conditions looking for the first threshold-based order
	 * rule (>=, >) and returns how far the order is from meeting it.
	 * Returns null if no such rule exists.
	 */
	private function computeAmountRemaining(Discount $coupon, Order $order): ?float
	{
		// Maps Commerce condition rule class → the Order property it compares against
		$ruleFieldMap = [
			ItemSubtotalConditionRule::class => 'itemSubtotal',
			ItemTotalConditionRule::class => 'itemTotal',
			TotalPriceConditionRule::class => 'totalPrice',
			TotalConditionRule::class => 'total',
		];

		foreach ($coupon->getTriggerCondition()->getConditionRules() as $triggerRule) {
			if (! $triggerRule instanceof OrderConditionRule) {
				continue;
			}

			foreach ($triggerRule->getOrderCondition()->getConditionRules() as $orderRule) {
				foreach ($ruleFieldMap as $ruleClass => $field) {
					if (
						$orderRule instanceof $ruleClass
						&& property_exists($orderRule, 'value')
						&& property_exists($orderRule, 'operator')
						&& $orderRule->value !== null
						&& in_array($orderRule->operator, ['>=', '>'], true)
					) {
						return max(0.0, (float) $orderRule->value - (float) $order->{$field});
					}
				}
			}
		}

		return null;
	}
}

<?php

namespace fostercommerce\advanceddiscounts\variables;

use Craft;
use craft\commerce\elements\conditions\orders\ItemSubtotalConditionRule;
use craft\commerce\elements\conditions\orders\ItemTotalConditionRule;
use craft\commerce\elements\conditions\orders\TotalConditionRule;
use craft\commerce\elements\conditions\orders\TotalPriceConditionRule;
use craft\commerce\elements\Order;
use fostercommerce\advanceddiscounts\elements\conditions\LineItemActionRule;
use fostercommerce\advanceddiscounts\elements\conditions\MessageActionRule;
use fostercommerce\advanceddiscounts\elements\conditions\OrderActionRule;
use fostercommerce\advanceddiscounts\elements\conditions\OrderConditionRule;
use fostercommerce\advanceddiscounts\enums\DiscountType;
use fostercommerce\advanceddiscounts\models\Discount;
use fostercommerce\advanceddiscounts\Plugin;

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

		foreach (Plugin::getInstance()->discounts->getAllDiscounts() as $discount) {
			if (! $discount->enabled) {
				continue;
			}

			if ($discount->code !== null && strcasecmp($discount->code, $order->couponCode ?? '') !== 0) {
				continue;
			}

			foreach ($discount->getActionCondition()->getConditionRules() as $rule) {
				if (! $rule instanceof MessageActionRule || $rule->message === '') {
					continue;
				}

				if (! $rule->getMessageCondition()->matchElement($order)) {
					continue;
				}

				$messages[] = $this->resolvePlaceholders($rule->message, $discount, $order);
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

	private function resolvePlaceholders(string $message, Discount $discount, Order $order): string
	{
		$placeholders = [];

		// {discountAmount} — value from the first action rule that has one
		foreach ($discount->getActionCondition()->getConditionRules() as $rule) {
			if (($rule instanceof OrderActionRule || $rule instanceof LineItemActionRule) && $rule->discountValue !== null) {
				$placeholders['{discountAmount}'] = $rule->discountType === DiscountType::Percentage
					? $rule->discountValue . '%'
					: Craft::$app->getFormatter()->asCurrency($rule->discountValue, $order->paymentCurrency);
				break;
			}
		}

		// {amountRemaining} — how much more the customer needs to spend
		$amountRemaining = $this->computeAmountRemaining($discount, $order);
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
	private function computeAmountRemaining(Discount $discount, Order $order): ?float
	{
		// Maps Commerce condition rule class → the Order property it compares against
		$ruleFieldMap = [
			ItemSubtotalConditionRule::class => 'itemSubtotal',
			ItemTotalConditionRule::class => 'itemTotal',
			TotalPriceConditionRule::class => 'totalPrice',
			TotalConditionRule::class => 'total',
		];

		foreach ($discount->getTriggerCondition()->getConditionRules() as $triggerRule) {
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

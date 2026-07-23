<?php

namespace fostercommerce\advanceddiscounts\base;

use Craft;
use craft\commerce\elements\conditions\orders\ItemSubtotalConditionRule;
use craft\commerce\elements\conditions\orders\ItemTotalConditionRule;
use craft\commerce\elements\conditions\orders\TotalConditionRule;
use craft\commerce\elements\conditions\orders\TotalPriceConditionRule;
use craft\commerce\elements\conditions\orders\TotalQtyConditionRule;
use craft\commerce\elements\Order;
use craft\commerce\models\LineItem;
use craft\commerce\models\OrderAdjustment;
use craft\helpers\MoneyHelper;
use fostercommerce\advanceddiscounts\elements\conditions\BogoCartActionRule;
use fostercommerce\advanceddiscounts\elements\conditions\BundleCondition;
use fostercommerce\advanceddiscounts\elements\conditions\HasPurchasableConditionRule;
use fostercommerce\advanceddiscounts\elements\conditions\LineItemCartActionRule;
use fostercommerce\advanceddiscounts\elements\conditions\LineItemConditionRule;
use fostercommerce\advanceddiscounts\elements\conditions\MessageActionRule;
use fostercommerce\advanceddiscounts\elements\conditions\OrderCartActionRule;
use fostercommerce\advanceddiscounts\elements\conditions\OrderConditionRule;
use fostercommerce\advanceddiscounts\elements\conditions\ShippingMethodCartActionRule;
use fostercommerce\advanceddiscounts\enums\DiscountType as DiscountValueType;
use fostercommerce\advanceddiscounts\helpers\Purchasables;
use fostercommerce\advanceddiscounts\models\Discount;
use fostercommerce\advanceddiscounts\models\DiscountPanel;
use Money\Money;

abstract class DiscountType implements DiscountTypeInterface
{
	public function getSettingsHtml(Discount $discount): string
	{
		return Craft::$app->getView()->renderTemplate('advanced-discounts/_groups', [
			'discount' => $discount,
			'actionLabel' => static::actionLabel(),
			'actionInstructions' => static::actionInstructions(),
			'bundle' => static::actionConditionClass() === BundleCondition::class,
		]);
	}

	public function getAdjustments(Order $order, Discount $discount): array
	{
		$adjustments = [];

		foreach ($discount->panels as $panel) {
			if (! $panel->enabled) {
				continue;
			}

			if (! $panel->getCartCondition()->matchElement($order)) {
				continue;
			}

			if ($panel->getCartActionCondition()->getConditionRules() !== []) {
				$name = $panel->name !== '' ? $panel->name : $discount->name;
				$panelAdjustments = $this->buildPanelAdjustments($panel, $order, $name);
				foreach ($panelAdjustments as $panelAdjustment) {
					$panelAdjustment->sourceSnapshot = [
						'advancedDiscountId' => $discount->id,
					];
				}

				array_push($adjustments, ...$panelAdjustments);
			}

			if ($panel->stopProcessing) {
				break;
			}
		}

		return $adjustments;
	}

	public function getMessages(Order $order, Discount $discount): array
	{
		$messages = [];

		foreach ($discount->panels as $panel) {
			if (! $panel->enabled) {
				continue;
			}

			$groupApplies = $panel->getCartCondition()->matchElement($order);

			foreach ($panel->getMessageCondition()->getConditionRules() as $rule) {
				if (! $rule instanceof MessageActionRule || $rule->message === '') {
					continue;
				}

				$messageCondition = $rule->getMessageCondition();
				$shows = $messageCondition->getConditionRules() !== []
					? $messageCondition->matchElement($order)
					: $groupApplies;

				if (! $shows) {
					continue;
				}

				$messages[] = $this->resolvePlaceholders($rule->message, $panel, $order);
			}
		}

		return $messages;
	}

	/**
	 * @return OrderAdjustment[]
	 */
	private function buildPanelAdjustments(DiscountPanel $panel, Order $order, string $discountName): array
	{
		$adjustments = [];
		$actionRules = $panel->getCartActionCondition()->getConditionRules();
		$includeRuleLabel = count($actionRules) > 1;

		foreach ($actionRules as $rule) {
			$name = $includeRuleLabel ? $discountName . ': ' . $rule->getLabel() : $discountName;

			if ($rule instanceof OrderCartActionRule) {
				$adjustment = $this->buildOrderAdjustment($rule, $order, $name);
				if ($adjustment !== null) {
					$adjustments[] = $adjustment;
				}
			} elseif ($rule instanceof ShippingMethodCartActionRule) {
				$adjustment = $this->buildShippingAdjustment($rule, $order, $name);
				if ($adjustment !== null) {
					$adjustments[] = $adjustment;
				}
			} elseif ($rule instanceof LineItemCartActionRule) {
				array_push($adjustments, ...$this->buildLineItemAdjustments($rule, $order, $name));
			} elseif ($rule instanceof BogoCartActionRule) {
				array_push($adjustments, ...$this->buildBogoAdjustments($rule, $order, $name));
			}
		}

		return $adjustments;
	}

	private function buildOrderAdjustment(OrderCartActionRule $rule, Order $order, string $discountName): ?OrderAdjustment
	{
		if (! $rule->discountValue) {
			return null;
		}

		$promotableSubtotal = $this->toMoney(0, $order);
		foreach ($order->getLineItems() as $lineItem) {
			if (! $lineItem->getIsPromotable()) {
				continue;
			}

			$promotableSubtotal = $promotableSubtotal->add($this->toMoney($lineItem->subtotal, $order));
		}

		if ($promotableSubtotal->isZero()) {
			return null;
		}

		$discount = $this->discountMoney($promotableSubtotal, $rule->discountType, $rule->discountValue);

		$adjustment = new OrderAdjustment();
		$adjustment->type = 'discount';
		$adjustment->name = $discountName;
		$adjustment->amount = -$this->toFloat($discount);
		$adjustment->orderId = $order->id;

		return $adjustment;
	}

	private function buildShippingAdjustment(ShippingMethodCartActionRule $rule, Order $order, string $discountName): ?OrderAdjustment
	{
		if (! $rule->discountValue) {
			return null;
		}

		if (! $rule->matchElement($order)) {
			return null;
		}

		$shippingCost = $order->getTotalShippingCost();
		if ($shippingCost <= 0) {
			return null;
		}

		$discount = $this->discountMoney($this->toMoney($shippingCost, $order), $rule->discountType, $rule->discountValue);

		$adjustment = new OrderAdjustment();
		$adjustment->type = 'discount';
		$adjustment->name = $discountName;
		$adjustment->amount = -$this->toFloat($discount);
		$adjustment->orderId = $order->id;

		return $adjustment;
	}

	/**
	 * @return OrderAdjustment[]
	 */
	private function buildLineItemAdjustments(LineItemCartActionRule $rule, Order $order, string $discountName): array
	{
		if (! $rule->discountValue) {
			return [];
		}

		$adjustments = [];

		foreach ($order->getLineItems() as $lineItem) {
			$purchasable = $lineItem->getPurchasable();
			if ($purchasable === null || ! $lineItem->getIsPromotable() || ! $rule->matchElement($purchasable)) {
				continue;
			}

			$base = $this->toMoney($lineItem->subtotal, $order);

			if ($rule->discountType === DiscountValueType::Percentage) {
				$discount = $base->multiply((string) $rule->discountValue)->divide('100');
			} else {
				$quantityFactor = $rule->applyPer === LineItemCartActionRule::APPLY_PER_PURCHASABLE ? $lineItem->qty : 1;
				$flat = $this->toMoney($rule->discountValue, $order)->multiply((string) $quantityFactor);
				$discount = $flat->greaterThan($base) ? $base : $flat;
			}

			$adjustment = new OrderAdjustment();
			$adjustment->type = 'discount';
			$adjustment->name = $discountName;
			$adjustment->amount = -$this->toFloat($discount);
			$adjustment->orderId = $order->id;
			$adjustment->lineItemId = $lineItem->id;
			$adjustment->setLineItem($lineItem);

			$adjustments[] = $adjustment;
		}

		return $adjustments;
	}

	/**
	 * @return OrderAdjustment[]
	 */
	private function buildBogoAdjustments(BogoCartActionRule $rule, Order $order, string $discountName): array
	{
		if (! $rule->discountValue) {
			return [];
		}

		$discountedQty = $rule->earnedQuantity($order);
		if ($discountedQty === 0) {
			return [];
		}

		$discountableUnits = [];

		foreach ($order->getLineItems() as $lineItem) {
			$purchasable = $lineItem->getPurchasable();
			if ($purchasable === null || ! $lineItem->getIsPromotable() || ! Purchasables::matches($purchasable, $rule->discountedPurchasableType, $rule->discountedPurchasableIds)) {
				continue;
			}

			$discountableUnits = array_merge($discountableUnits, array_fill(0, $lineItem->qty, $lineItem));
		}

		// Discount the cheapest qualifying units, not the most expensive
		usort(
			$discountableUnits,
			static fn (LineItem $firstLineItem, LineItem $secondLineItem): int => $firstLineItem->salePrice <=> $secondLineItem->salePrice
		);

		$amountsByLineItem = [];
		$lineItemsByKey = [];

		foreach (array_slice($discountableUnits, 0, $discountedQty) as $lineItem) {
			$unitDiscount = $this->discountMoney($this->toMoney($lineItem->salePrice, $order), $rule->discountType, $rule->discountValue);

			$key = spl_object_id($lineItem);
			$amountsByLineItem[$key] = isset($amountsByLineItem[$key]) ? $amountsByLineItem[$key]->add($unitDiscount) : $unitDiscount;
			$lineItemsByKey[$key] = $lineItem;
		}

		$adjustments = [];

		foreach ($amountsByLineItem as $key => $discount) {
			$lineItem = $lineItemsByKey[$key];

			$adjustment = new OrderAdjustment();
			$adjustment->type = 'discount';
			$adjustment->name = $discountName;
			$adjustment->amount = -$this->toFloat($discount);
			$adjustment->orderId = $order->id;
			$adjustment->lineItemId = $lineItem->id;
			$adjustment->setLineItem($lineItem);

			$adjustments[] = $adjustment;
		}

		return $adjustments;
	}

	private function toMoney(float $value, Order $order): Money
	{
		$money = MoneyHelper::toMoney([
			'value' => (string) $value,
			'currency' => (string) $order->currency,
		]);

		if ($money === false) {
			throw new \RuntimeException("Could not build a money value for “{$value} {$order->currency}”.");
		}

		return $money;
	}

	private function discountMoney(Money $base, string $discountType, float $discountValue): Money
	{
		if ($discountType === DiscountValueType::Percentage) {
			return $base->multiply((string) $discountValue)->divide('100');
		}

		$flat = MoneyHelper::toMoney([
			'value' => (string) $discountValue,
			'currency' => $base->getCurrency(),
		]);

		if ($flat === false) {
			throw new \RuntimeException("Could not build a money value for the discount amount “{$discountValue}”.");
		}

		return $flat->greaterThan($base) ? $base : $flat;
	}

	private function toFloat(Money $money): float
	{
		return (float) MoneyHelper::toDecimal($money);
	}

	private function resolvePlaceholders(string $message, DiscountPanel $panel, Order $order): string
	{
		$placeholders = [];

		foreach ($panel->getCartActionCondition()->getConditionRules() as $rule) {
			if (($rule instanceof OrderCartActionRule || $rule instanceof LineItemCartActionRule || $rule instanceof BogoCartActionRule) && $rule->discountValue !== null) {
				$placeholders['{discountAmount}'] = $rule->discountType === DiscountValueType::Percentage
					? $rule->discountValue . '%'
					: Craft::$app->getFormatter()->asCurrency($rule->discountValue, $order->paymentCurrency);
				break;
			}
		}

		$amountRemaining = $this->computeAmountRemaining($panel, $order);
		if ($amountRemaining !== null) {
			$placeholders['{amountRemaining}'] = Craft::$app->getFormatter()->asCurrency($amountRemaining, $order->paymentCurrency);
		}

		$quantityRemaining = $this->computeQuantityRemaining($panel, $order);
		if ($quantityRemaining !== null) {
			$placeholders['{quantityRemaining}'] = $quantityRemaining;
		}

		$bogoRule = $this->firstBogoRule($panel);
		if ($bogoRule !== null) {
			$placeholders['{buyQuantityRemaining}'] = $bogoRule->buyQuantityRemaining($order);
			$placeholders['{discountedQuantity}'] = $bogoRule->earnedQuantity($order);
		}

		return strtr($message, $placeholders);
	}

	private function firstBogoRule(DiscountPanel $panel): ?BogoCartActionRule
	{
		foreach ($panel->getCartActionCondition()->getConditionRules() as $rule) {
			if ($rule instanceof BogoCartActionRule) {
				return $rule;
			}
		}

		return null;
	}

	private function computeAmountRemaining(DiscountPanel $panel, Order $order): ?float
	{
		$ruleFieldMap = [
			ItemSubtotalConditionRule::class => ['itemSubtotal', 'subtotal'],
			ItemTotalConditionRule::class => ['itemTotal', 'total'],
			TotalPriceConditionRule::class => ['totalPrice', 'total'],
			TotalConditionRule::class => ['total', 'total'],
		];

		foreach ($panel->getCartCondition()->getConditionRules() as $triggerRule) {
			if (! $triggerRule instanceof OrderConditionRule) {
				continue;
			}

			foreach ($triggerRule->getOrderCondition()->getConditionRules() as $orderRule) {
				foreach ($ruleFieldMap as $ruleClass => [$orderField, $lineItemField]) {
					if (
						$orderRule instanceof $ruleClass
						&& property_exists($orderRule, 'value')
						&& property_exists($orderRule, 'operator')
						&& $orderRule->value !== null
						&& in_array($orderRule->operator, ['>=', '>'], true)
					) {
						$promotableValue = (float) $order->{$orderField};
						foreach ($order->getLineItems() as $lineItem) {
							if (! $lineItem->getIsPromotable()) {
								$promotableValue -= (float) $lineItem->{$lineItemField};
							}
						}

						return max(0.0, (float) $orderRule->value - $promotableValue);
					}
				}
			}
		}

		return null;
	}

	private function computeQuantityRemaining(DiscountPanel $panel, Order $order): ?int
	{
		foreach ($panel->getCartCondition()->getConditionRules() as $triggerRule) {
			if ($triggerRule instanceof OrderConditionRule) {
				foreach ($triggerRule->getOrderCondition()->getConditionRules() as $orderRule) {
					if (
						$orderRule instanceof TotalQtyConditionRule
						&& $orderRule->value !== null
						&& in_array($orderRule->operator, ['>=', '>'], true)
					) {
						return max(0, (int) $orderRule->value - $order->totalQty);
					}
				}
			}

			if ($triggerRule instanceof LineItemConditionRule) {
				foreach ($triggerRule->getLineItemCondition()->getConditionRules() as $rule) {
					if (
						$rule instanceof HasPurchasableConditionRule
						&& $rule->quantity !== null
						&& in_array($rule->operator, ['>=', '>'], true)
					) {
						$purchasableId = (int) $rule->getElementId();
						$totalQty = 0;

						foreach ($order->getLineItems() as $lineItem) {
							$purchasable = $lineItem->getPurchasable();
							if ($purchasable !== null && Purchasables::matches($purchasable, $rule->purchasableType, [$purchasableId])) {
								$totalQty += $lineItem->qty;
							}
						}

						return max(0, $rule->quantity - $totalQty);
					}
				}
			}
		}

		return null;
	}
}

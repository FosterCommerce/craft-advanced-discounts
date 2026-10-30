<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\helpers;

use craft\commerce\elements\Order;
use craft\commerce\models\LineItem;
use craft\commerce\models\OrderAdjustment;
use craft\helpers\MoneyHelper;
use Money\Money;

/**
 * Caps discount adjustments at what is left of each line item, the promotable subtotal and shipping.
 */
final class RemainingDiscount
{
	/**
	 * @var array<string, Money>
	 */
	private array $byLineItem = [];

	private Money $promotable;

	private Money $shipping;

	private readonly string $currency;

	public function __construct(Order $order)
	{
		$this->currency = (string) $order->currency;
		$this->promotable = $this->toMoney(0);
		$this->shipping = $this->toMoney($order->getTotalShippingCost());

		foreach ($order->getLineItems() as $lineItem) {
			if ($lineItem->getIsPromotable()) {
				$this->byLineItem[$this->lineItemKey($lineItem)] = $this->toMoney($lineItem->subtotal);
			}
		}

		// Count discounts from outside this plugin, such as Commerce's own, before ours
		$otherDiscounts = $this->toMoney(0);
		foreach ($order->getAdjustmentsByType('discount') as $adjustment) {
			if (isset($adjustment->sourceSnapshot['advancedDiscountId'])) {
				continue;
			}

			$lineItem = $adjustment->getLineItem();
			$key = $lineItem instanceof LineItem ? $this->lineItemKey($lineItem) : null;
			if ($key !== null && isset($this->byLineItem[$key])) {
				$this->byLineItem[$key] = $this->floorAtZero($this->byLineItem[$key]->add($this->toMoney($adjustment->amount)));
			} elseif ($key === null) {
				$otherDiscounts = $otherDiscounts->add($this->toMoney($adjustment->amount));
			}
		}

		foreach ($this->byLineItem as $lineRemaining) {
			$this->promotable = $this->promotable->add($lineRemaining);
		}

		$this->promotable = $this->floorAtZero($this->promotable->add($otherDiscounts));
	}

	/**
	 * Reduce each adjustment to what is left, dropping any with nothing left to take.
	 *
	 * @param OrderAdjustment[] $adjustments
	 * @return OrderAdjustment[]
	 */
	public function cap(array $adjustments): array
	{
		$capped = [];

		foreach ($adjustments as $adjustment) {
			$allowed = $this->take($adjustment);
			if ($allowed->isZero()) {
				continue;
			}

			$adjustment->amount = -(float) MoneyHelper::toDecimal($allowed);
			$capped[] = $adjustment;
		}

		return $capped;
	}

	private function take(OrderAdjustment $adjustment): Money
	{
		$requested = $this->toMoney(-$adjustment->amount);

		if (($adjustment->sourceSnapshot['rule'] ?? null) === 'shipping') {
			$allowed = Money::min($requested, $this->shipping);
			$this->shipping = $this->shipping->subtract($allowed);

			return $allowed;
		}

		$lineItem = $adjustment->getLineItem();
		if ($lineItem instanceof LineItem) {
			$key = $this->lineItemKey($lineItem);
			$lineRemaining = $this->byLineItem[$key] ?? $this->toMoney(0);
			$allowed = Money::min($requested, $lineRemaining, $this->promotable);
			$this->byLineItem[$key] = $lineRemaining->subtract($allowed);
		} else {
			$allowed = Money::min($requested, $this->promotable);
		}

		$this->promotable = $this->promotable->subtract($allowed);

		return $allowed;
	}

	private function floorAtZero(Money $money): Money
	{
		return $money->isNegative() ? $this->toMoney(0) : $money;
	}

	private function toMoney(float $value): Money
	{
		return Amounts::toMoney($value, $this->currency);
	}

	// Key an unsaved line item by object, since it has no ID yet
	private function lineItemKey(LineItem $lineItem): string
	{
		return $lineItem->id !== null ? "id:{$lineItem->id}" : 'object:' . spl_object_id($lineItem);
	}
}

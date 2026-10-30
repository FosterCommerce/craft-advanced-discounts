<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\base;

use Craft;
use craft\commerce\elements\Order;
use craft\commerce\models\LineItem;
use craft\commerce\models\OrderAdjustment;
use craft\helpers\MoneyHelper;
use fostercommerce\advanceddiscounts\elements\conditions\BogoCartActionRule;
use fostercommerce\advanceddiscounts\elements\conditions\BundleCondition;
use fostercommerce\advanceddiscounts\elements\conditions\LineItemCartActionRule;
use fostercommerce\advanceddiscounts\elements\conditions\MessageActionRule;
use fostercommerce\advanceddiscounts\elements\conditions\OrderCartActionRule;
use fostercommerce\advanceddiscounts\elements\conditions\ShippingMethodCartActionRule;
use fostercommerce\advanceddiscounts\enums\DiscountType as DiscountValueType;
use fostercommerce\advanceddiscounts\helpers\Amounts;
use fostercommerce\advanceddiscounts\helpers\PromotableThreshold;
use fostercommerce\advanceddiscounts\helpers\Purchasables;
use fostercommerce\advanceddiscounts\models\Discount;
use fostercommerce\advanceddiscounts\models\DiscountPanel;
use Money\Money;

abstract class DiscountType implements DiscountTypeInterface
{
	/**
	 * @var array<string, string> Token => translation key describing the value it resolves to
	 */
	public const MESSAGE_PLACEHOLDERS = [
		'{discountAmount}' => 'messagePlaceholders.discountAmount',
		'{amountRemaining}' => 'messagePlaceholders.amountRemaining',
		'{quantityRemaining}' => 'messagePlaceholders.quantityRemaining',
		'{buyQuantityRemaining}' => 'messagePlaceholders.buyQuantityRemaining',
		'{discountedQuantity}' => 'messagePlaceholders.discountedQuantity',
	];

	/**
	 * @var string[] Tokens that only resolve for Buy X, Get Y discounts
	 */
	private const BUNDLE_ONLY_MESSAGE_PLACEHOLDERS = ['{buyQuantityRemaining}', '{discountedQuantity}'];

	/**
	 * @return array<string, string>
	 */
	public static function messagePlaceholders(): array
	{
		return self::filterMessagePlaceholders(static::actionConditionClass() === BundleCondition::class);
	}

	/**
	 * @return array<string, string>
	 */
	public static function filterMessagePlaceholders(bool $bundle): array
	{
		if ($bundle) {
			return self::MESSAGE_PLACEHOLDERS;
		}

		return array_diff_key(self::MESSAGE_PLACEHOLDERS, array_flip(self::BUNDLE_ONLY_MESSAGE_PLACEHOLDERS));
	}

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

			if (! PromotableThreshold::matches($panel->getCartCondition(), $order)) {
				continue;
			}

			$name = $panel->name !== '' ? $panel->name : $discount->name;
			array_push($adjustments, ...$this->buildPanelAdjustments($discount, $panel, $order, $name));

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

			$groupApplies = PromotableThreshold::matches($panel->getCartCondition(), $order);

			foreach ($panel->getMessageCondition()->getConditionRules() as $rule) {
				if (! $rule instanceof MessageActionRule) {
					continue;
				}

				if ($rule->message === '') {
					continue;
				}

				$messageCondition = $rule->getMessageCondition();
				$shows = $messageCondition->getConditionRules() !== []
					? PromotableThreshold::matches($messageCondition, $order)
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
	private function buildPanelAdjustments(Discount $discount, DiscountPanel $panel, Order $order, string $adjustmentName): array
	{
		$adjustments = [];
		$actionRules = $panel->getCartActionCondition()->getConditionRules();
		$includeRuleLabel = count($actionRules) > 1;

		foreach ($actionRules as $actionRule) {
			$name = $includeRuleLabel ? $adjustmentName . ': ' . $actionRule->getLabel() : $adjustmentName;

			if ($actionRule instanceof OrderCartActionRule) {
				$ruleHandle = 'order';
				$adjustment = $this->buildOrderAdjustment($actionRule, $order, $name);
				$ruleAdjustments = $adjustment instanceof OrderAdjustment ? [$adjustment] : [];
			} elseif ($actionRule instanceof ShippingMethodCartActionRule) {
				$ruleHandle = 'shipping';
				$adjustment = $this->buildShippingAdjustment($actionRule, $order, $name);
				$ruleAdjustments = $adjustment instanceof OrderAdjustment ? [$adjustment] : [];
			} elseif ($actionRule instanceof LineItemCartActionRule) {
				$ruleHandle = 'lineItem';
				$ruleAdjustments = $this->buildLineItemAdjustments($actionRule, $panel, $order, $name);
			} elseif ($actionRule instanceof BogoCartActionRule) {
				$ruleHandle = 'bogo';
				$ruleAdjustments = $this->buildBogoAdjustments($actionRule, $order, $name);
			} else {
				continue;
			}

			if ($ruleAdjustments === []) {
				continue;
			}

			$snapshot = [
				'advancedDiscountId' => $discount->id,
				'rule' => $ruleHandle,
				'discountType' => $actionRule->discountType,
				'discountValue' => $actionRule->discountValue,
				'requireCouponCode' => $discount->requireCouponCode,
				'couponCode' => $discount->requireCouponCode ? $order->couponCode : null,
			];

			foreach ($ruleAdjustments as $ruleAdjustment) {
				$ruleAdjustment->sourceSnapshot = array_merge($ruleAdjustment->sourceSnapshot, $snapshot);
			}

			array_push($adjustments, ...$ruleAdjustments);
		}

		return $adjustments;
	}

	private function buildOrderAdjustment(OrderCartActionRule $rule, Order $order, string $discountName): ?OrderAdjustment
	{
		if (! $rule->discountValue) {
			return null;
		}

		$promotableSubtotal = $this->toMoney(0, $order);
		$discountedPurchasableIds = [];

		foreach ($order->getLineItems() as $lineItem) {
			if (! $lineItem->getIsPromotable()) {
				continue;
			}

			$promotableSubtotal = $promotableSubtotal->add($this->toMoney($lineItem->subtotal, $order));
			$discountedPurchasableIds[] = $lineItem->purchasableId;
		}

		if ($promotableSubtotal->isZero()) {
			return null;
		}

		$discount = $this->discountMoney($promotableSubtotal, $rule->discountType, $rule->discountValue);

		$adjustment = $this->newAdjustment($order, $discountName, $discount);
		$adjustment->sourceSnapshot = [
			'discountedPurchasableIds' => $discountedPurchasableIds,
		];

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

		return $this->newAdjustment($order, $discountName, $discount);
	}

	/**
	 * @return OrderAdjustment[]
	 */
	private function buildLineItemAdjustments(LineItemCartActionRule $rule, DiscountPanel $panel, Order $order, string $discountName): array
	{
		if (! $rule->discountValue) {
			return [];
		}

		$usesCartCondition = $rule->lineItemsFilter === LineItemCartActionRule::FILTER_CART_CONDITION;
		$cartConditionVariantIds = $usesCartCondition ? $panel->getCartConditionVariantIds() : [];

		$adjustments = [];

		foreach ($order->getLineItems() as $lineItem) {
			$purchasable = $lineItem->getPurchasable();
			if ($purchasable === null) {
				continue;
			}

			if (! $lineItem->getIsPromotable()) {
				continue;
			}

			$matches = $usesCartCondition
				? in_array((int) $purchasable->id, $cartConditionVariantIds, true)
				: $rule->matchElement($purchasable);

			if (! $matches) {
				continue;
			}

			$base = $this->toMoney($lineItem->subtotal, $order);

			if ($rule->discountType === DiscountValueType::Percentage->value) {
				$discount = $this->discountMoney($base, $rule->discountType, $rule->discountValue);
			} else {
				$quantityFactor = $rule->applyPer === LineItemCartActionRule::APPLY_PER_PURCHASABLE ? $lineItem->qty : 1;
				$discount = $this->toMoney($rule->discountValue, $order)->multiply((string) $quantityFactor);
			}

			$adjustments[] = $this->newAdjustment($order, $discountName, $discount, $lineItem);
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
			if ($purchasable === null) {
				continue;
			}

			if (! $lineItem->getIsPromotable()) {
				continue;
			}

			if (! Purchasables::matches($purchasable, $rule->discountedPurchasableType, $rule->discountedPurchasableIds)) {
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

			$adjustments[] = $this->newAdjustment($order, $discountName, $discount, $lineItem);
		}

		return $adjustments;
	}

	private function newAdjustment(Order $order, string $name, Money $discount, ?LineItem $lineItem = null): OrderAdjustment
	{
		$adjustment = new OrderAdjustment();
		$adjustment->type = 'discount';
		$adjustment->name = $name;
		$adjustment->amount = -$this->toFloat($discount);
		$adjustment->orderId = $order->id;

		if ($lineItem instanceof LineItem) {
			$adjustment->lineItemId = $lineItem->id;
			$adjustment->setLineItem($lineItem);
			$adjustment->sourceSnapshot = [
				'discountedPurchasableIds' => [$lineItem->purchasableId],
			];
		}

		return $adjustment;
	}

	private function toMoney(float $value, Order $order): Money
	{
		return Amounts::toMoney($value, (string) $order->currency);
	}

	private function discountMoney(Money $base, string $discountType, float $discountValue): Money
	{
		if ($discountType === DiscountValueType::Percentage->value) {
			return $base->multiply((string) $discountValue)->divide('100');
		}

		return Amounts::toMoney($discountValue, $base->getCurrency()->getCode());
	}

	private function toFloat(Money $money): float
	{
		return (float) MoneyHelper::toDecimal($money);
	}

	private function resolvePlaceholders(string $message, DiscountPanel $panel, Order $order): string
	{
		$placeholders = [];

		foreach ($panel->getCartActionCondition()->getConditionRules() as $conditionRule) {
			if (($conditionRule instanceof OrderCartActionRule || $conditionRule instanceof LineItemCartActionRule || $conditionRule instanceof BogoCartActionRule || $conditionRule instanceof ShippingMethodCartActionRule) && $conditionRule->discountValue !== null) {
				$placeholders['{discountAmount}'] = $conditionRule->discountType === DiscountValueType::Percentage->value
					? $conditionRule->discountValue . '%'
					: MoneyHelper::toString(Amounts::toMoney($conditionRule->discountValue, (string) $order->currency));
				break;
			}
		}

		$amountRemaining = PromotableThreshold::amountRemaining($panel->getCartCondition(), $order);
		if ($amountRemaining !== null) {
			$placeholders['{amountRemaining}'] = MoneyHelper::toString(Amounts::toMoney($amountRemaining, (string) $order->currency));
		}

		$quantityRemaining = PromotableThreshold::quantityRemaining($panel->getCartCondition(), $order);
		if ($quantityRemaining !== null) {
			$placeholders['{quantityRemaining}'] = $quantityRemaining;
		}

		$bogoRule = $this->firstBogoRule($panel);
		if ($bogoRule instanceof BogoCartActionRule) {
			$placeholders['{buyQuantityRemaining}'] = $bogoRule->buyQuantityRemaining($order);
			// One message has to read correctly both before and after the customer qualifies.
			$placeholders['{discountedQuantity}'] = (int) $bogoRule->discountedQuantity;
		}

		$message = strtr($message, $placeholders);

		$unresolved = array_diff_key(self::MESSAGE_PLACEHOLDERS, $placeholders);
		if ($unresolved !== []) {
			return str_replace(array_keys($unresolved), '', $message);
		}

		return $message;
	}

	private function firstBogoRule(DiscountPanel $panel): ?BogoCartActionRule
	{
		foreach ($panel->getCartActionCondition()->getConditionRules() as $conditionRule) {
			if ($conditionRule instanceof BogoCartActionRule) {
				return $conditionRule;
			}
		}

		return null;
	}
}

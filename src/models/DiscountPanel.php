<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\models;

use craft\base\Model;
use craft\commerce\elements\Variant;
use craft\elements\conditions\ElementConditionInterface;
use craft\helpers\StringHelper;
use fostercommerce\advanceddiscounts\elements\conditions\BogoCartActionRule;
use fostercommerce\advanceddiscounts\elements\conditions\BundleCondition;
use fostercommerce\advanceddiscounts\elements\conditions\CartActionCondition;
use fostercommerce\advanceddiscounts\elements\conditions\CartCondition;
use fostercommerce\advanceddiscounts\elements\conditions\HasPurchasableConditionRule;
use fostercommerce\advanceddiscounts\elements\conditions\LineItemCartActionRule;
use fostercommerce\advanceddiscounts\elements\conditions\LineItemConditionRule;
use fostercommerce\advanceddiscounts\elements\conditions\MessageActionRule;
use fostercommerce\advanceddiscounts\elements\conditions\MessageCondition;
use fostercommerce\advanceddiscounts\elements\conditions\RelatedToConditionRule;
use fostercommerce\advanceddiscounts\helpers\NestedConditionConfig;
use fostercommerce\advanceddiscounts\helpers\Purchasables;

class DiscountPanel extends Model
{
	public string $key = '';

	public string $name = '';

	public bool $enabled = true;

	public bool $stopProcessing = false;

	/**
	 * @var class-string<ElementConditionInterface>
	 */
	public string $actionConditionClass = CartActionCondition::class;

	private ?ElementConditionInterface $_cartCondition = null;

	private ?ElementConditionInterface $_cartActionCondition = null;

	private ?ElementConditionInterface $_messageCondition = null;

	public function init(): void
	{
		parent::init();

		if ($this->key === '') {
			$this->key = StringHelper::UUID();
		}
	}

	public function getCartCondition(): ElementConditionInterface
	{
		$condition = $this->_cartCondition ?? new CartCondition();
		$condition->mainTag = 'div';
		$condition->name = "panels[{$this->key}][cartCondition]";

		return $condition;
	}

	/**
	 * @param ElementConditionInterface|string|array<string, mixed>|null $condition
	 */
	public function setCartCondition(ElementConditionInterface|string|array|null $condition): void
	{
		$this->_cartCondition = $this->normalizeCondition($condition, CartCondition::class);
	}

	public function getCartActionCondition(): ElementConditionInterface
	{
		$condition = $this->_cartActionCondition ?? new $this->actionConditionClass();
		$condition->mainTag = 'div';
		$condition->name = "panels[{$this->key}][cartActionCondition]";

		if ($condition instanceof BundleCondition && $condition->getConditionRules() === []) {
			$condition->setConditionRules([new BogoCartActionRule()]);
		}

		return $condition;
	}

	/**
	 * @param ElementConditionInterface|string|array<string, mixed>|null $condition
	 */
	public function setCartActionCondition(ElementConditionInterface|string|array|null $condition): void
	{
		$this->_cartActionCondition = $this->normalizeCondition($condition, $this->actionConditionClass);
	}

	public function getMessageCondition(): ElementConditionInterface
	{
		$condition = $this->_messageCondition ?? new MessageCondition();
		$condition->mainTag = 'div';
		$condition->name = "panels[{$this->key}][messageCondition]";
		if ($condition instanceof MessageCondition) {
			$condition->bundle = $this->actionConditionClass === BundleCondition::class;
		}

		return $condition;
	}

	/**
	 * @param ElementConditionInterface|string|array<string, mixed>|null $condition
	 */
	public function setMessageCondition(ElementConditionInterface|string|array|null $condition): void
	{
		$this->_messageCondition = $this->normalizeCondition($condition, MessageCondition::class);
	}

	public function hasMessageErrors(): bool
	{
		foreach ($this->getMessageCondition()->getConditionRules() as $rule) {
			if ($rule instanceof MessageActionRule && $rule->hasErrors('message')) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getConfig(): array
	{
		return [
			'name' => $this->name,
			'enabled' => $this->enabled,
			'stopProcessing' => $this->stopProcessing,
			'cartCondition' => $this->getCartCondition()->getConfig(),
			'cartActionCondition' => $this->getCartActionCondition()->getConfig(),
			'messageCondition' => $this->getMessageCondition()->getConfig(),
		];
	}

	/**
	 * @return int[]
	 */
	public function getCartConditionVariantIds(): array
	{
		$variantIds = [];

		foreach ($this->getCartCondition()->getConditionRules() as $cartConditionRule) {
			if (! $cartConditionRule instanceof LineItemConditionRule) {
				continue;
			}

			foreach ($cartConditionRule->getLineItemCondition()->getConditionRules() as $lineItemRule) {
				if ($lineItemRule instanceof HasPurchasableConditionRule) {
					$purchasableId = (int) $lineItemRule->getElementId();
					if ($purchasableId !== 0) {
						$variantIds = array_merge($variantIds, Purchasables::expandToVariantIds($lineItemRule->purchasableType, [$purchasableId]));
					}
				} elseif ($lineItemRule instanceof RelatedToConditionRule) {
					$relatedToId = (int) $lineItemRule->getElementId();
					if ($relatedToId !== 0) {
						$variantIds = array_merge($variantIds, Purchasables::relatedVariantIds($relatedToId));
					}
				}
			}
		}

		return array_values(array_unique($variantIds));
	}

	/**
	 * @return Variant[]
	 */
	public function getNonPromotableVariants(): array
	{
		$variantIds = [];
		foreach ($this->getCartActionCondition()->getConditionRules() as $rule) {
			if ($rule instanceof LineItemCartActionRule) {
				$variantIds = array_merge($variantIds, $rule->lineItemsFilter === LineItemCartActionRule::FILTER_CART_CONDITION
					? $this->getCartConditionVariantIds()
					: Purchasables::expandToVariantIds($rule->purchasableType, $rule->purchasableIds));
			} elseif ($rule instanceof BogoCartActionRule) {
				$variantIds = array_merge(
					$variantIds,
					Purchasables::expandToVariantIds($rule->buyPurchasableType, $rule->buyPurchasableIds),
					Purchasables::expandToVariantIds($rule->discountedPurchasableType, $rule->discountedPurchasableIds)
				);
			}
		}

		if ($variantIds === []) {
			return [];
		}

		$variants = Variant::find()
			->id(array_unique($variantIds))
			->status(null)
			->all();

		return array_values(array_filter($variants, static fn (Variant $variant): bool => ! $variant->getIsPromotable()));
	}

	/**
	 * @param ElementConditionInterface|string|array<string, mixed>|null $condition
	 * @param class-string<ElementConditionInterface> $conditionClass
	 */
	private function normalizeCondition(ElementConditionInterface|string|array|null $condition, string $conditionClass): ElementConditionInterface
	{
		return NestedConditionConfig::build($condition, $conditionClass);
	}
}

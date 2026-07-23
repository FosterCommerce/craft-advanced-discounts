<?php

namespace fostercommerce\advanceddiscounts\models;

use Craft;
use craft\base\Model;
use craft\commerce\elements\Variant;
use craft\elements\conditions\ElementConditionInterface;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use fostercommerce\advanceddiscounts\elements\conditions\BogoCartActionRule;
use fostercommerce\advanceddiscounts\elements\conditions\BundleCondition;
use fostercommerce\advanceddiscounts\elements\conditions\CartActionCondition;
use fostercommerce\advanceddiscounts\elements\conditions\CartCondition;
use fostercommerce\advanceddiscounts\elements\conditions\LineItemCartActionRule;
use fostercommerce\advanceddiscounts\elements\conditions\MessageCondition;
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

	public null|ElementConditionInterface $_cartCondition = null;

	public null|ElementConditionInterface $_cartActionCondition = null;

	public null|ElementConditionInterface $_messageCondition = null;

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

		return $condition;
	}

	/**
	 * @param ElementConditionInterface|string|array<string, mixed>|null $condition
	 */
	public function setMessageCondition(ElementConditionInterface|string|array|null $condition): void
	{
		$this->_messageCondition = $this->normalizeCondition($condition, MessageCondition::class);
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
	 * @return Variant[]
	 */
	public function getNonPromotableVariants(): array
	{
		$variantIds = [];
		foreach ($this->getCartActionCondition()->getConditionRules() as $rule) {
			if ($rule instanceof LineItemCartActionRule) {
				$variantIds = array_merge($variantIds, Purchasables::expandToVariantIds($rule->purchasableType, $rule->purchasableIds));
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
		if ($condition === null) {
			$condition = [];
		}

		if (is_string($condition)) {
			$condition = Json::decodeIfJson($condition);
		}

		if (! $condition instanceof ElementConditionInterface) {
			$condition['class'] = $conditionClass;
			/** @phpstan-ignore-next-line */
			$condition = Craft::$app->getConditions()->createCondition($condition);
			/** @var ElementConditionInterface $condition */
		}
		$condition->forProjectConfig = false;

		return $condition;
	}
}

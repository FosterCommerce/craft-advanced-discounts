<?php

namespace fostercommerce\coupons\elements\conditions;

use Craft;
use craft\base\conditions\BaseConditionRule;
use craft\base\ElementInterface;
use craft\commerce\elements\db\OrderQuery;
use craft\commerce\elements\Order;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\helpers\Cp;
use craft\helpers\Html;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use fostercommerce\coupons\enums\DiscountType;
use fostercommerce\coupons\enums\ItemsChoice;
use yii\base\InvalidConfigException;

class OrderActionRule extends BaseConditionRule implements ElementConditionRuleInterface
{
	public string $discountType = DiscountType::FlatAmount;

	public ?float $discountValue = null;

	public string $itemsChoice = ItemsChoice::AllItems;

	public ?float $numberOfItems = null;

	/**
	 * @see getOrderActionCondition()
	 * @see setOrderActionCondition()
	 */
	public null|ElementConditionInterface $_orderActionCondition = null;

	/**
	 * @var array<int, mixed>
	 */
	protected array $_values = [];

	protected bool $reloadOnOperatorChange = true;

	public function __construct($config = [])
	{
		$config['orderActionCondition'] = $config['attributes']['orderActionCondition'] ?? [];

		parent::__construct($config);
	}

	public function getOrderActionCondition(): ElementConditionInterface
	{
		$condition = $this->_orderActionCondition ?? new OrderActionCondition();
		$condition->mainTag = 'div';
		$condition->name = 'orderActionCondition';

		return $condition;
	}

	/**
	 * @param ElementConditionInterface|string|array<string, mixed> $condition
	 */
	public function setOrderActionCondition(ElementConditionInterface|string|array $condition): void
	{
		if (is_string($condition)) {
			$condition = Json::decodeIfJson($condition);
		}

		if (! $condition instanceof ElementConditionInterface) {
			$condition['class'] = OrderActionCondition::class;
			/** @phpstan-ignore-next-line */
			$condition = Craft::$app->getConditions()->createCondition($condition);
			/** @var ElementConditionInterface $condition */
		}
		$condition->forProjectConfig = false;

		$this->_orderActionCondition = $condition;
	}

	public function getLabel(): string
	{
		return Craft::t('coupons', 'Order');
	}

	public function getExclusiveQueryParams(): array
	{
		return [];
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getConfig(): array
	{
		return array_merge(parent::getConfig(), [
			'itemsChoice' => $this->itemsChoice,
			'numberOfItems' => $this->numberOfItems,
			'discountType' => $this->discountType,
			'discountValue' => $this->discountValue,
			'orderActionCondition' => $this->getOrderActionCondition()->getConfig(),
		]);
	}

	public function modifyQuery(\craft\elements\db\ElementQueryInterface $query): void
	{
		// todo
		/** @var orderquery $query */
		$query->shippingMethodHandle($this->paramValue());
	}

	public function matchElement(ElementInterface $element): bool
	{
		// todo
		/** @var Order $element */
		return $this->matchValue($element->shippingMethodHandle);
	}

	protected function inputHtml(): string
	{
		if ($this->discountType === DiscountType::FlatAmount) {
			$discountTypeLabel = Craft::t('coupons', 'Flat Amount');
		} elseif ($this->discountType === DiscountType::Percentage) {
			$discountTypeLabel = Craft::t('coupons', 'Percentage');
		} else {
			$discountTypeLabel = '';
		}

		$itemsChoiceHtml = '';
		if ($this->itemsChoice === ItemsChoice::NumberOfItems) {
			$itemsChoiceHtml =
				Html::hiddenLabel(Craft::t('coupons', 'Discount value'), 'numberOfItems') .
				Cp::textHtml([
					'type' => 'number',
					'id' => 'numberOfItems',
					'name' => 'numberOfItems',
					'value' => $this->numberOfItems,
					'autocomplete' => false,
					'placeholder' => Craft::t('coupons', 'Up to number of items'),
					'class' => 'flex-grow flex-shrink',
				]);
		}

		return Html::beginTag('div', [
			'class' => ['flex', 'flex-start', 'flex-grow'],
			'style' => [
				'flex-direction' => 'column',
			],
		]) .
			Html::beginTag('div', [
				'class' => ['flex', 'flex-start', 'flex-grow'],
			]) .
			Html::hiddenLabel(Html::encode($this->getLabel()), 'itemsChoice') .
			Cp::selectHtml([
				'id' => 'itemsChoice',
				'name' => 'itemsChoice',
				'options' => [
					ItemsChoice::AllItems => Craft::t('coupons', 'All items'),
					ItemsChoice::NumberOfItems => Craft::t('coupons', 'Number of items'),
				],
				'value' => $this->itemsChoice,
				'inputAttributes' => [
					'hx' => [
						'post' => UrlHelper::actionUrl('conditions/render'),
					],
				],
			]) .
			Html::hiddenLabel(Craft::t('coupons', 'Discount Type'), 'discountType') .
			$itemsChoiceHtml .
			Cp::selectHtml([
				'id' => 'discountType',
				'name' => 'discountType',
				'options' => [
					DiscountType::FlatAmount => Craft::t('coupons', 'Discount a flat amount'),
					DiscountType::Percentage => Craft::t('coupons', 'Discount a percentage'),
				],
				'value' => $this->discountType,
				'inputAttributes' => [
					'hx' => [
						'post' => UrlHelper::actionUrl('conditions/render'),
					],
				],
			]) .
			Html::hiddenLabel(Craft::t('coupons', 'Discount value'), 'discountValue') .
			Cp::textHtml([
				'type' => 'number',
				'id' => 'discountValue',
				'name' => 'discountValue',
				'value' => $this->discountValue,
				'autocomplete' => false,
				'placeholder' => $discountTypeLabel,
				'class' => 'flex-grow flex-shrink',
			]) .
			Html::endTag('div') .
			$this->getOrderActionCondition()->getBuilderHtml() .
			Html::endTag('div');
	}

	protected function paramValue(): mixed
	{
		return null;
	}

	/**
	 * @return array<int, mixed>
	 */
	protected function defineRules(): array
	{
		return array_merge(parent::defineRules(), [
			[['discountType', 'discountValue', 'itemsChoice', 'numberOfItems'], 'safe'],
		]);
	}

	/**
	 * @param array<int, mixed>|string|null $value
	 */
	protected function matchValue(array|string|null $value): bool
	{
		// todo override this correctly
		if (! $this->_values) {
			return true;
		}

		if ($value === '' || $value === null) {
			$value = [];
		} else {
			$value = (array) $value;
		}

		return match ($this->operator) {
			self::OPERATOR_IN => ! empty(array_intersect($value, $this->_values)),
			self::OPERATOR_NOT_IN => empty(array_intersect($value, $this->_values)),
			default => throw new InvalidConfigException("Invalid operator: {$this->operator}"),
		};
	}
}

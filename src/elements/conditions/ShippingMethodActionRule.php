<?php

namespace fostercommerce\coupons\elements\conditions;

use Craft;
use craft\base\conditions\BaseMultiSelectConditionRule;
use craft\base\ElementInterface;
use craft\commerce\elements\db\OrderQuery;
use craft\commerce\elements\Order;
use craft\commerce\Plugin;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\helpers\ArrayHelper;
use craft\helpers\Cp;
use craft\helpers\Html;
use craft\helpers\UrlHelper;
use fostercommerce\coupons\enums\DiscountType;
use yii\base\InvalidConfigException;

class ShippingMethodActionRule extends BaseMultiSelectConditionRule implements ElementConditionRuleInterface
{
	private const ANY_VALUE = '';

	public string $operator = self::OPERATOR_EQ;

	public string $discountType = DiscountType::FlatAmount;

	public ?float $discountValue = null;

	protected bool $reloadOnOperatorChange = true;

	public function getLabel(): string
	{
		return Craft::t('coupons', 'Shipping Method');
	}

	public function getExclusiveQueryParams(): array
	{
		return [];
	}

	public function setValues(array|string $values): void
	{
		if ($this->operator === self::OPERATOR_EQ && $values === '') {
			parent::setValues([self::ANY_VALUE]);
		} else {
			parent::setValues($values);
		}
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getConfig(): array
	{
		return array_merge(parent::getConfig(), [
			'discountType' => $this->discountType,
			'discountValue' => $this->discountValue,
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

	/**
	 * @return array<int, string>
	 */
	protected function operators(): array
	{
		return [
			self::OPERATOR_EQ,
			...parent::operators(),
		];
	}

	protected function options(): array
	{
		return $this->shippingMethodOptions(false);
	}

	protected function inputHtml(): string
	{
		if ($this->operator === self::OPERATOR_EQ) {
			$selectId = 'select';

			$value = $this->getValues()[0] ?? self::ANY_VALUE;
			$selectHtml =
				Html::hiddenLabel(Html::encode($this->getLabel()), $selectId) .
				Cp::selectHtml([
					'id' => $selectId,
					'name' => 'values',
					'options' => $this->shippingMethodOptions(true),
					'value' => $value,
				]);
		} else {
			$selectHtml = parent::inputHtml();
		}

		if ($this->discountType === DiscountType::FlatAmount) {
			$discountTypeLabel = Craft::t('coupons', 'Flat Amount');
		} elseif ($this->discountType === DiscountType::Percentage) {
			$discountTypeLabel = Craft::t('coupons', 'Percentage');
		} else {
			$discountTypeLabel = '';
		}

		return $selectHtml .
			Html::hiddenLabel(Craft::t('coupons', 'Discount Type'), 'discountType') .
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
			]);
	}

	/**
	 * @return array<int, mixed>
	 */
	protected function defineRules(): array
	{
		return array_merge(parent::defineRules(), [
			[['values', 'discountType', 'discountValue'], 'safe'],
		]);
	}

	protected function matchValue(array|string|null $value): bool
	{
		// todo override this correctly
		$values = $this->getValues();

		if (! $values) {
			return true;
		}

		if ($value === '' || $value === null) {
			$value = [];
		} else {
			$value = (array) $value;
		}

		return match ($this->operator) {
			self::OPERATOR_IN => ! empty(array_intersect($value, $values)),
			self::OPERATOR_NOT_IN => empty(array_intersect($value, $values)),
			default => throw new InvalidConfigException("Invalid operator: {$this->operator}"),
		};
	}

	/**
	 * @return array<string, string>
	 */
	private function shippingMethodOptions(bool $includeAny = false): array
	{
		$shippingMethods = ArrayHelper::map(Plugin::getInstance()->getShippingMethods()->getAllShippingMethods()->toArray(), 'handle', 'name');

		if ($includeAny) {
			return [
				self::ANY_VALUE => 'Any',
				...$shippingMethods,
			];
		}
		return $shippingMethods;
	}
}

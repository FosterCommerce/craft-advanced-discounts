<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\elements\conditions;

use Craft;
use craft\base\conditions\BaseMultiSelectConditionRule;
use craft\base\ElementInterface;
use craft\commerce\elements\Order;
use craft\commerce\Plugin;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\ArrayHelper;
use craft\helpers\Cp;
use craft\helpers\Html;
use yii\base\InvalidConfigException;

class ShippingMethodCartActionRule extends BaseMultiSelectConditionRule implements ElementConditionRuleInterface
{
	use DiscountValueTrait;

	private const ANY_VALUE = '';

	public string $operator = self::OPERATOR_EQ;

	public string $discountType = 'flatAmount';

	public ?float $discountValue = null;

	protected bool $reloadOnOperatorChange = true;

	public function getLabel(): string
	{
		return Craft::t('advanced-discounts', 'rules.shipping');
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

	public function modifyQuery(ElementQueryInterface $query): void
	{
	}

	public function matchElement(ElementInterface $element): bool
	{
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


		return $selectHtml .
			$this->discountValueInputHtml();
	}

	/**
	 * @return array<int, mixed>
	 */
	protected function defineRules(): array
	{
		return array_merge(parent::defineRules(), [
			[['values'], 'safe'],
		], $this->discountValueRules());
	}

	protected function matchValue(array|string|null $value): bool
	{
		$values = $this->getValues();

		if ($this->operator === self::OPERATOR_EQ) {
			$handle = $values[0] ?? self::ANY_VALUE;
			return $handle === self::ANY_VALUE || $handle === $value;
		}

		if ($values === []) {
			return true;
		}

		return match ($this->operator) {
			self::OPERATOR_IN => in_array($value, $values, true),
			self::OPERATOR_NOT_IN => ! in_array($value, $values, true),
			default => throw new InvalidConfigException("Invalid operator: {$this->operator}"),
		};
	}

	/**
	 * @return array<string, string>
	 */
	private function shippingMethodOptions(bool $includeAny): array
	{
		/** @var Plugin $commerce */
		$commerce = Plugin::getInstance();
		$shippingMethods = ArrayHelper::map($commerce->getShippingMethods()->getAllShippingMethods()->toArray(), 'handle', 'name');

		if ($includeAny) {
			return [
				self::ANY_VALUE => Craft::t('advanced-discounts', 'rules.shipping.anyMethod'),
				...$shippingMethods,
			];
		}

		return $shippingMethods;
	}
}

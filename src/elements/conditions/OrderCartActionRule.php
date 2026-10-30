<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\elements\conditions;

use Craft;
use craft\base\conditions\BaseConditionRule;
use craft\base\ElementInterface;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Html;

class OrderCartActionRule extends BaseConditionRule implements ElementConditionRuleInterface
{
	use DiscountValueTrait;

	public string $discountType = 'flatAmount';

	public ?float $discountValue = null;

	public function getLabel(): string
	{
		return Craft::t('advanced-discounts', 'rules.itemSubtotal');
	}

	public function getExclusiveQueryParams(): array
	{
		return [];
	}

	public function modifyQuery(ElementQueryInterface $query): void
	{
	}

	public function matchElement(ElementInterface $element): bool
	{
		return true;
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

	protected function inputHtml(): string
	{
		return Html::tag(
			'div',
			$this->discountValueInputHtml(),
			[
				'class' => ['flex', 'flex-start', 'flex-grow'],
			]
		);
	}

	/**
	 * @return array<int, mixed>
	 */
	protected function defineRules(): array
	{
		return array_merge(parent::defineRules(), $this->discountValueRules());
	}
}

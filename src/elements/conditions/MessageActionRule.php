<?php

namespace fostercommerce\advancedDiscounts\elements\conditions;

use Craft;
use craft\base\conditions\BaseConditionRule;
use craft\base\ElementInterface;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Cp;
use craft\helpers\Html;

class MessageActionRule extends BaseConditionRule implements ElementConditionRuleInterface
{
	public string $message = '';

	public function getLabel(): string
	{
		return Craft::t('advanced-discounts', 'Message');
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
			'message' => $this->message,
		]);
	}

	protected function inputHtml(): string
	{
		return Html::hiddenLabel(Craft::t('advanced-discounts', 'Message'), 'message') .
			Cp::textHtml([
				'id' => 'message',
				'name' => 'message',
				'value' => $this->message,
				'placeholder' => Craft::t('advanced-discounts', 'e.g. Spend another {amount} to get {discount} off'),
				'class' => 'flex-grow',
			]);
	}

	/**
	 * @return array<int, mixed>
	 */
	protected function defineRules(): array
	{
		return array_merge(parent::defineRules(), [
			[['message'], 'safe'],
		]);
	}
}

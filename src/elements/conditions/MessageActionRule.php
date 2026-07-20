<?php

namespace fostercommerce\advanceddiscounts\elements\conditions;

use Craft;
use craft\base\conditions\BaseConditionRule;
use craft\base\ElementInterface;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Cp;
use craft\helpers\Html;
use craft\helpers\Json;

class MessageActionRule extends BaseConditionRule implements ElementConditionRuleInterface
{
	public string $message = '';

	public ?ElementConditionInterface $_messageCondition = null;

	public function __construct($config = [])
	{
		$config['messageCondition'] = isset($config['messageCondition']) ? $config['messageCondition'] : ($config['attributes']['messageCondition'] ?? []);
		parent::__construct($config);
	}

	public function getMessageCondition(): ElementConditionInterface
	{
		$condition = $this->_messageCondition ?? new AndCondition();
		$condition->mainTag = 'div';
		$condition->name = 'messageCondition';

		return $condition;
	}

	/**
	 * @param ElementConditionInterface|string|array<string, mixed> $condition
	 */
	public function setMessageCondition(ElementConditionInterface|string|array $condition): void
	{
		if (is_string($condition)) {
			$condition = Json::decodeIfJson($condition);
		}

		if (! $condition instanceof ElementConditionInterface) {
			if (empty($condition)) {
				return;
			}
			$condition['class'] = AndCondition::class;
			/** @phpstan-ignore-next-line */
			$condition = Craft::$app->getConditions()->createCondition($condition);
			/** @var ElementConditionInterface $condition */
		}
		$condition->forProjectConfig = false;

		$this->_messageCondition = $condition;
	}

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
			'messageCondition' => $this->_messageCondition?->getConfig() ?? [],
		]);
	}

	protected function inputHtml(): string
	{
		return Html::tag(
			'div',
			Html::tag(
				'div',
				Html::hiddenLabel(Craft::t('advanced-discounts', 'Message'), 'message') .
				Cp::textareaHtml([
					'id' => 'message',
					'name' => 'message',
					'value' => $this->message,
					'placeholder' => Craft::t('advanced-discounts', 'e.g. Spend another {amountRemaining} to get {discountAmount} off'),
					'class' => 'flex-grow',
					'rows' => 3,
				]),
				[
					'class' => ['flex', 'flex-start', 'flex-grow'],
				]
			) .
			Html::tag('p', Craft::t('advanced-discounts', 'Create rules to determine when to show this message'), [
				'class' => 'instructions',
			]) .
			$this->getMessageCondition()->getBuilderHtml(),
			[
				'class' => ['flex', 'flex-start', 'flex-grow'],
				'style' => [
					'flex-direction' => 'column',
				],
			]
		);
	}

	/**
	 * @return array<int, mixed>
	 */
	protected function defineRules(): array
	{
		return array_merge(parent::defineRules(), [
			[['message', 'messageCondition'], 'safe'],
		]);
	}
}

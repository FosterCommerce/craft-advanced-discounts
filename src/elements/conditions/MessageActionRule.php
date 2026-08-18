<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\elements\conditions;

use Craft;
use craft\base\conditions\BaseConditionRule;
use craft\base\ElementInterface;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Cp;
use craft\helpers\Html;
use fostercommerce\advanceddiscounts\base\DiscountType;
use fostercommerce\advanceddiscounts\helpers\NestedConditionConfig;

class MessageActionRule extends BaseConditionRule implements ElementConditionRuleInterface
{
	public string $message = '';

	private ?ElementConditionInterface $_messageCondition = null;

	public function __construct($config = [])
	{
		$config['messageCondition'] = NestedConditionConfig::extract($config, 'messageCondition');
		parent::__construct($config);
	}

	public function getMessageCondition(): ElementConditionInterface
	{
		$condition = $this->_messageCondition ?? new CartCondition();
		$condition->mainTag = 'div';
		$condition->name = 'messageCondition';

		return $condition;
	}

	/**
	 * @param ElementConditionInterface|string|array<string, mixed> $condition
	 */
	public function setMessageCondition(ElementConditionInterface|string|array $condition): void
	{
		if ($condition === [] || $condition === '') {
			return;
		}

		$this->_messageCondition = NestedConditionConfig::build($condition, CartCondition::class);
	}

	public function getLabel(): string
	{
		return Craft::t('advanced-discounts', 'rules.message');
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

	/**
	 * @return string[] Placeholder tokens used in `message` that don't apply to this discount type.
	 */
	public function getInapplicablePlaceholders(): array
	{
		if ($this->message === '') {
			return [];
		}

		$condition = $this->getCondition();
		$bundle = $condition instanceof MessageCondition && $condition->bundle;
		$inapplicable = array_diff_key(DiscountType::MESSAGE_PLACEHOLDERS, DiscountType::filterMessagePlaceholders($bundle));

		return array_values(array_filter(
			array_keys($inapplicable),
			fn (string $token): bool => str_contains($this->message, $token)
		));
	}

	public function validateMessagePlaceholders(string $attribute): void
	{
		foreach ($this->getInapplicablePlaceholders() as $token) {
			$this->addError($attribute, Craft::t('advanced-discounts', 'edit.error.tokenUnavailable', [
				'token' => $token,
			]));
		}
	}

	protected function inputHtml(): string
	{
		$condition = $this->getCondition();
		$placeholders = DiscountType::filterMessagePlaceholders($condition instanceof MessageCondition && $condition->bundle);

		return Html::tag(
			'div',
			Html::tag(
				'div',
				Html::hiddenLabel(Craft::t('advanced-discounts', 'rules.message'), 'message') .
				Cp::textareaHtml([
					'id' => 'message',
					'name' => 'message',
					'value' => $this->message,
					'placeholder' => Craft::t('advanced-discounts', 'rules.message.placeholder'),
					'class' => 'flex-grow',
					'rows' => 3,
				]),
				[
					'class' => ['flex', 'flex-start', 'flex-grow'],
				]
			) .
			($this->hasErrors('message')
				? Craft::$app->getView()->renderTemplate('_includes/forms/errorList.twig', [
					'errors' => $this->getErrors('message'),
				])
				: '') .
			Html::tag(
				'div',
				implode('', array_map(
					static fn (string $token, string $description): string => Html::tag('span', $token, [
						'class' => 'advanced-discount-token-chip',
						'draggable' => 'true',
						'data' => [
							'token' => $token,
						],
						'title' => Craft::t('advanced-discounts', $description),
						'tabindex' => '0',
						'role' => 'button',
					]),
					array_keys($placeholders),
					$placeholders
				)),
				[
					'class' => ['flex', 'flex-wrap', 'advanced-discount-token-chips'],
				]
			) .
			Html::tag('p', Craft::t('advanced-discounts', 'rules.message.showWhenInstructions'), [
				'class' => ['instructions', 'advanced-discount-message-instructions'],
			]) .
			$this->getMessageCondition()->getBuilderHtml(),
			[
				'class' => array_filter([
					'flex',
					'flex-start',
					'flex-grow',
					'advanced-discount-message',
					$this->hasErrors('message') ? 'has-errors' : null,
				]),
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
			[['message'], 'validateMessagePlaceholders'],
		]);
	}
}

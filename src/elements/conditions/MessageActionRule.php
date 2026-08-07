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
use fostercommerce\advanceddiscounts\base\DiscountType;

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
		if (is_string($condition)) {
			$condition = Json::decodeIfJson($condition);
		}

		if (! $condition instanceof ElementConditionInterface) {
			if (empty($condition)) {
				return;
			}
			$condition['class'] = CartCondition::class;
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
			$this->addError($attribute, Craft::t('advanced-discounts', '{token} isn’t available for this discount type.', [
				'token' => $token,
			]));
		}
	}

	protected function inputHtml(): string
	{
		$condition = $this->getCondition();
		$placeholders = DiscountType::filterMessagePlaceholders($condition instanceof MessageCondition && $condition->bundle);

		Craft::$app->getView()->registerJs(<<<'JS'
			(function() {
				var insertToken = function(textarea, token) {
					var start = textarea.selectionStart ?? textarea.value.length;
					var end = textarea.selectionEnd ?? textarea.value.length;
					var value = textarea.value;
					textarea.value = value.slice(0, start) + token + value.slice(end);
					var caret = start + token.length;
					textarea.setSelectionRange(caret, caret);
					textarea.focus();
					$(textarea).trigger('input').trigger('change');
				};

				$(document).off('.advancedDiscountsTokenChip');

				$(document).on('click.advancedDiscountsTokenChip keydown.advancedDiscountsTokenChip', '.advanced-discount-token-chip', function(ev) {
					if (ev.type === 'keydown' && ev.key !== 'Enter' && ev.key !== ' ') {
						return;
					}
					ev.preventDefault();
					var textarea = $(this).closest('.advanced-discount-message').find('textarea').get(0);
					if (textarea) {
						insertToken(textarea, $(this).data('token'));
					}
				});

				$(document).on('dragstart.advancedDiscountsTokenChip', '.advanced-discount-token-chip', function(ev) {
					ev.originalEvent.dataTransfer.setData('text/plain', $(this).data('token'));
					ev.originalEvent.dataTransfer.effectAllowed = 'copy';
				});

				$(document).on('dragover.advancedDiscountsTokenChip', '.advanced-discount-message textarea', function(ev) {
					ev.preventDefault();
				});

				$(document).on('drop.advancedDiscountsTokenChip', '.advanced-discount-message textarea', function(ev) {
					ev.preventDefault();
					var token = ev.originalEvent.dataTransfer.getData('text/plain');
					if (token) {
						insertToken(this, token);
					}
				});
			})();
			JS);

		return Html::tag('style', <<<'CSS'
			.advanced-discount-token-chips {
				gap: 6px;
				margin: 6px 0 0;
			}
			.advanced-discount-token-chip {
				background: var(--gray-100);
				border: 1px solid var(--gray-200);
				border-radius: 12px;
				color: var(--gray-700);
				cursor: grab;
				font-size: 12px;
				padding: 2px 10px;
				user-select: none;
			}
			.advanced-discount-token-chip:hover,
			.advanced-discount-token-chip:focus-visible {
				background: var(--gray-150);
				border-color: var(--gray-300);
			}
			.advanced-discount-message.has-errors {
				border-radius: var(--medium-border-radius);
				padding: 8px;
			}
			CSS) .
		Html::tag(
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
			Html::tag('p', Craft::t('advanced-discounts', 'Create rules to determine when to show this message'), [
				'class' => 'instructions',
				'style' => [
					'margin' => '10px 0 4px',
				],
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
			[['message'], 'validateMessagePlaceholders'],
		]);
	}
}

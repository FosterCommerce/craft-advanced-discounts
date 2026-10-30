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
use craft\models\Site;
use fostercommerce\advanceddiscounts\base\DiscountType;
use fostercommerce\advanceddiscounts\helpers\NestedConditionConfig;
use fostercommerce\advanceddiscounts\helpers\SiteText;

class MessageActionRule extends BaseConditionRule implements ElementConditionRuleInterface
{
	public string $message = '';

	/**
	 * @var array<string, string> Messages for sites other than the primary site, keyed by site UID
	 */
	private array $_siteMessages = [];

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

	/**
	 * @return array<string, string>
	 */
	public function getSiteMessages(): array
	{
		return $this->_siteMessages;
	}

	public function setSiteMessages(mixed $siteMessages): void
	{
		$this->_siteMessages = SiteText::filter($siteMessages);
	}

	public function getMessageForSite(Site $site): string
	{
		return $this->_siteMessages[$site->uid] ?? $this->message;
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
			'siteMessages' => $this->_siteMessages,
			'messageCondition' => $this->_messageCondition?->getConfig() ?? [],
		]);
	}

	/**
	 * @return string[] Placeholder tokens used in any site's message that don't apply to this discount type.
	 */
	public function getInapplicablePlaceholders(): array
	{
		$messages = implode("\n", [$this->message, ...array_values($this->_siteMessages)]);
		if (trim($messages) === '') {
			return [];
		}

		$condition = $this->getCondition();
		$bundle = $condition instanceof MessageCondition && $condition->bundle;
		$inapplicable = array_diff_key(DiscountType::MESSAGE_PLACEHOLDERS, DiscountType::filterMessagePlaceholders($bundle));

		return array_values(array_filter(
			array_keys($inapplicable),
			fn (string $token): bool => str_contains($messages, $token)
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
			Html::tag('div', $this->messageInputsHtml(), [
				'class' => ['flex-grow', 'advanced-discount-message-inputs'],
			]) .
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
			[['message', 'siteMessages', 'messageCondition'], 'safe'],
			[['message'], 'validateMessagePlaceholders'],
		]);
	}

	private function messageInputsHtml(): string
	{
		$sites = Craft::$app->getSites();
		$primarySite = $sites->getPrimarySite();
		$html = $this->messageInputHtml($primarySite, 'message', $this->message, Craft::t('advanced-discounts', 'rules.message.placeholder'));

		if (! Craft::$app->getIsMultiSite()) {
			return $html;
		}

		$otherSites = array_filter($sites->getAllSites(), static fn (Site $site): bool => ! $site->primary);
		foreach ($otherSites as $otherSite) {
			// Show the primary message as the placeholder, since a blank site message uses it
			$html .= $this->messageInputHtml($otherSite, "siteMessages[{$otherSite->uid}]", $this->_siteMessages[$otherSite->uid] ?? '', $this->message);
		}

		return $html;
	}

	private function messageInputHtml(Site $site, string $name, string $value, string $placeholder): string
	{
		$id = $site->primary ? 'message' : "message-{$site->id}";

		return Cp::fieldHtml(Cp::textareaHtml([
			'id' => $id,
			'name' => $name,
			'value' => $value,
			'placeholder' => $placeholder,
			'rows' => 3,
		]), [
			'id' => $id,
			'label' => Craft::t('advanced-discounts', 'rules.message'),
			'siteId' => $site->id,
			'fieldClass' => array_filter(['flex-grow', $site->primary ? null : 'hidden']),
			'fieldAttributes' => [
				'data' => [
					'site-text' => $site->uid,
				],
			],
		]);
	}
}

<?php

namespace fostercommerce\coupons\elements\conditions;

use Craft;
use craft\base\conditions\BaseConditionRule;
use craft\base\ElementInterface;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Html;

class TriggerConditionRule extends BaseConditionRule implements NestedConditionRuleInterface
{
	public ?ElementConditionInterface $_triggerCondition = null;

	public function getNestedCondition(): ElementConditionInterface
	{
		return $this->getTriggerCondition();
	}

	public function getTriggerCondition(): ElementConditionInterface
	{
		$condition = $this->_triggerCondition ?? new TriggerCondition();
		$condition->mainTag = 'div';

		return $condition;
	}

	/**
	 * @param ElementConditionInterface|array<string, mixed> $condition
	 */
	public function setTriggerCondition(ElementConditionInterface|array $condition): void
	{
		if (is_array($condition)) {
			if (empty($condition)) {
				return;
			}
			$condition['class'] = TriggerCondition::class;
			/** @phpstan-ignore-next-line */
			$condition = Craft::$app->getConditions()->createCondition($condition);
			/** @var ElementConditionInterface $condition */
		}
		$condition->forProjectConfig = false;

		$this->_triggerCondition = $condition;
	}

	public function getLabel(): string
	{
		return Craft::t('coupons', 'Trigger');
	}

	public function getExclusiveQueryParams(): array
	{
		return [];
	}

	public function modifyQuery(ElementQueryInterface $query): void
	{
		// TODO
		/*        $elementId = $this->getElementId();
				if ($elementId !== null) {
					$query->andRelatedTo($elementId);
				}*/
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getConfig(): array
	{
		return array_merge(parent::getConfig(), [
			'triggerCondition' => $this->_triggerCondition?->getConfig() ?? [],
		]);
	}

	public function matchElement(ElementInterface $element): bool
	{
		// todo
		return $element::find()
			->id($element->id ?: false)
			->site('*')
			->drafts($element->getIsDraft())
			->provisionalDrafts($element->isProvisionalDraft)
			->revisions($element->getIsRevision())
			->status(null)
			->exists();
	}

	protected function inputHtml(): string
	{
		return Html::tag('div', $this->getTriggerCondition()->getBuilderHtml());
	}

	/**
	 * @return array<int, mixed>
	 */
	protected function defineRules(): array
	{
		return array_merge(parent::defineRules(), [
		]);
	}
}

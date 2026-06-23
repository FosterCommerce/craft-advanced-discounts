<?php

namespace fostercommerce\coupons\elements\conditions;

use Craft;
use craft\base\conditions\ConditionRuleInterface;
use craft\elements\conditions\ElementCondition;
use Illuminate\Support\Collection;
use yii\base\InvalidConfigException;

class AndTriggerCondition extends ElementCondition
{
	public function init(): void
	{
		$this->addRuleLabel = Craft::t('coupons', 'AND');
		parent::init();
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getConfig(): array
	{
		$conditionRules = Collection::make($this->getConditionRules());
		return array_merge($this->config(), [
			'class' => static::class,
			'conditionRules' => $conditionRules
				->map(function (ConditionRuleInterface $rule) {
					if (! $rule instanceof NestedConditionRuleInterface) {
						return null;
					}
					try {
						return $rule->getConfig();
					} catch (InvalidConfigException) {
						// The rule is misconfigured
						return null;
					}
				})
				->filter(fn (?array $config) => $config !== null)
				->values()
				->all(),
		]);
	}

	/**
	 * @return array<int, class-string>
	 */
	protected function selectableConditionRules(): array
	{
		return [
			TriggerConditionRule::class,
			OrderConditionRule::class,
		];
	}
}

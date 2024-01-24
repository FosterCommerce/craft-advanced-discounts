<?php
namespace fostercommerce\coupons\elements\conditions;

use Craft;
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

    public function getConfig(): array
    {
        $conditionRules = Collection::make($this->getConditionRules());
        return array_merge($this->config(), [
            'class' => get_class($this),
            'conditionRules' => $conditionRules
                ->map(function(NestedConditionRuleInterface $rule) {
                    try {
                        return [
                            ...$rule->getConfig(),
                            'condition' => $rule->getNestedCondition()->getConfig(),
                        ];
                    } catch (InvalidConfigException) {
                        // The rule is misconfigured
                        return null;
                    }
                })
                ->filter(fn(?array $config) => $config !== null)
                ->values()
                ->all(),
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function conditionRuleTypes(): array
    {
        return [
            TriggerConditionRule::class,
            OrderConditionRule::class,
        ];
    }
}

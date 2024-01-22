<?php
namespace fostercommerce\coupons\elements\conditions;

use craft\elements\conditions\ElementCondition;
use fostercommerce\coupons\elements\conditions\RelatedToConditionRule;

class AndTriggerCondition extends ElementCondition
{
    public ?string $addRuleLabel = "AND";

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

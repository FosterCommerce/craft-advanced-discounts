<?php
namespace fostercommerce\coupons\elements\conditions;

use craft\commerce\elements\conditions\orders\HasPurchasableConditionRule;
use craft\elements\conditions\ElementCondition;

class ApplyCondition extends ElementCondition
{
    /**
     * @inheritdoc
     */
    protected function conditionRuleTypes(): array
    {
        return array_merge([
            RelatedToConditionRule::class,
            HasPurchasableConditionRule::class,
        ]);
    }
}

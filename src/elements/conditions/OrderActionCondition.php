<?php
namespace fostercommerce\coupons\elements\conditions;

use craft\elements\conditions\ElementCondition;
use fostercommerce\coupons\elements\conditions\RelatedToConditionRule;

class OrderActionCondition extends ElementCondition
{
    public ?string $addRuleLabel = "Add condition";
    /**
     * @inheritdoc
     */
    protected function conditionRuleTypes(): array
    {
        return array_merge([
            HasPurchasableConditionRule::class,
            RelatedToConditionRule::class,
        ]);
    }
}

<?php
namespace fostercommerce\coupons\elements\conditions;

use craft\elements\conditions\ElementCondition;
use fostercommerce\coupons\elements\conditions\RelatedToConditionRule;

class RelatedToCondition extends ElementCondition
{
    /**
     * @inheritdoc
     */
    protected function conditionRuleTypes(): array
    {
        return array_merge([
            RelatedToConditionRule::class,
        ]);
    }
}

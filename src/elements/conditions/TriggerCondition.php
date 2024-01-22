<?php
namespace fostercommerce\coupons\elements\conditions;

use craft\commerce\elements\conditions\orders\ItemSubtotalConditionRule;
use craft\commerce\elements\conditions\orders\ItemTotalConditionRule;
use craft\commerce\elements\conditions\orders\TotalConditionRule;
use craft\commerce\elements\conditions\orders\TotalPriceConditionRule;
use craft\commerce\elements\conditions\orders\TotalQtyConditionRule;
use craft\elements\conditions\ElementCondition;
use fostercommerce\coupons\elements\conditions\RelatedToConditionRule;

class TriggerCondition extends ElementCondition
{
    public ?string $addRuleLabel = "OR";
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

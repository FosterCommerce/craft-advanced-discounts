<?php
namespace fostercommerce\coupons\elements\conditions;

use craft\elements\conditions\ElementCondition;
use fostercommerce\coupons\elements\conditions\RelatedToConditionRule;

class TriggerCondition extends ElementCondition
{
    public function init(): void
    {
        $this->addRuleLabel = Craft::t('coupons', 'OR');
        parent::init();
    }

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

<?php
namespace fostercommerce\coupons\elements\conditions;

use Craft;
use craft\elements\conditions\ElementCondition;
use fostercommerce\coupons\elements\conditions\RelatedToConditionRule;

class ActionCondition extends ElementCondition
{
    public function init(): void
    {
        $this->addRuleLabel = Craft::t('coupons', 'Add an action');
        parent::init();
    }

    /**
     * @inheritdoc
     */
    protected function conditionRuleTypes(): array
    {
        return array_merge([
            OrderActionRule::class,
            ShippingMethodActionRule::class,
        ]);
    }
}

<?php

namespace fostercommerce\coupons\elements\conditions;

use Craft;
use craft\base\BlockElementInterface;
use craft\base\conditions\BaseConditionRule;
use craft\base\ElementInterface;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\elements\db\ElementQueryInterface;
use craft\elements\Entry;
use craft\helpers\Cp;
use craft\helpers\DateTimeHelper;
use craft\helpers\Html;
use craft\helpers\Json;
use craft\helpers\Typecast;
use craft\helpers\UrlHelper;

class OrderConditionRule extends BaseConditionRule implements ElementConditionRuleInterface
{
    public ?ElementConditionInterface $_orderCondition = null;

    public function __construct($config = [])
    {
        $config['orderCondition'] = $config['attributes']['condition']??[];

        parent::__construct($config);
    }

    /**
     * @return ElementConditionInterface
     */
    public function getOrderCondition(): ElementConditionInterface
    {
        $condition = $this->_orderCondition ?? new OrderCondition(null, ['mainTag' => 'div']);

        return $condition;
    }

    /**
     * @param ElementConditionInterface|array $condition
     * @return void
     */
    public function setOrderCondition(ElementConditionInterface|array $condition): void
    {
        if (!$condition instanceof ElementConditionInterface) {
            $condition['class'] = OrderCondition::class;
            /** @var OrderCondition $condition */
            $condition = Craft::$app->getConditions()->createCondition($condition);
        }
        $condition->forProjectConfig = false;

        $this->_orderCondition = $condition;
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return Craft::t('coupons', 'Order');
    }

    /**
     * @inheritdoc
     */
    public function getExclusiveQueryParams(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function modifyQuery(ElementQueryInterface $query): void
    {
        // TODO
        /*        $elementId = $this->getElementId();
                if ($elementId !== null) {
                    $query->andRelatedTo($elementId);
                }*/
    }

    /**
     * @inheritdoc
     */
    protected function inputHtml(): string
    {
        return Html::tag('div', $this->getOrderCondition()->getBuilderHtml());
    }

    /**
     * @inheritdoc
     */
    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getConfig(): array
    {
        return array_merge(parent::getConfig(), [
        ]);
    }

    /**
     * @inheritdoc
     */
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
}

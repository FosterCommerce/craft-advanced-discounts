<?php

namespace fostercommerce\coupons\elements\conditions;

use Craft;
use craft\base\conditions\BaseConditionRule;
use craft\base\ElementInterface;
use craft\elements\conditions\ElementConditionRuleInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Cp;
use craft\helpers\DateTimeHelper;
use craft\helpers\Html;

class DateRangeConditionRule extends BaseConditionRule implements ElementConditionRuleInterface
{
	private ?string $_startDate = null;
	private ?string $_endDate = null;

	public function init(): void
	{
		if ($this->_startDate === null) {
			$this->_startDate = DateTimeHelper::toIso8601(DateTimeHelper::now());
		}
		parent::init();
	}

	public function getStartDate(): ?string
	{
		return $this->_startDate;
	}

	public function setStartDate(mixed $value): void
	{
		$this->_startDate = $value ? DateTimeHelper::toIso8601($value) : null;
	}

	public function getEndDate(): ?string
	{
		return $this->_endDate;
	}

	public function setEndDate(mixed $value): void
	{
		$this->_endDate = $value ? DateTimeHelper::toIso8601($value) : null;
	}

	public function getLabel(): string
	{
		return Craft::t('coupons', 'Date Range');
	}

	public function getExclusiveQueryParams(): array
	{
		return [];
	}

	public function modifyQuery(ElementQueryInterface $query): void
	{
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getConfig(): array
	{
		return array_merge(parent::getConfig(), [
			'startDate' => $this->_startDate,
			'endDate' => $this->_endDate,
		]);
	}

	public function matchElement(ElementInterface $element): bool
	{
		$now = DateTimeHelper::now();

		if ($this->_startDate !== null) {
			$start = DateTimeHelper::toDateTime($this->_startDate);
			if ($start && $now < $start) {
				return false;
			}
		}

		if ($this->_endDate !== null) {
			$end = DateTimeHelper::toDateTime($this->_endDate);
			if ($end) {
				// End date is inclusive: valid through the end of the specified day
				$end->modify('+1 day');
				if ($now >= $end) {
					return false;
				}
			}
		}

		return true;
	}

	protected function inputHtml(): string
	{
		$startHtml = Html::tag(
			'div',
			Html::label(Craft::t('coupons', 'From'), 'start-date-date') .
			Html::tag('div', Cp::dateHtml([
				'id' => 'start-date',
				'name' => 'startDate',
				'value' => $this->_startDate,
			])),
			['class' => ['flex', 'flex-nowrap']]
		);

		$endHtml = Html::tag(
			'div',
			Html::label(Craft::t('coupons', 'To'), 'end-date-date') .
			Html::tag('div', Cp::dateHtml([
				'id' => 'end-date',
				'name' => 'endDate',
				'value' => $this->_endDate,
			])),
			['class' => ['flex', 'flex-nowrap']]
		);

		return Html::tag('div', $startHtml . $endHtml, ['class' => 'flex']);
	}

	/**
	 * @return array<int, mixed>
	 */
	protected function defineRules(): array
	{
		return array_merge(parent::defineRules(), [
			[['startDate'], 'required'],
			[['startDate', 'endDate'], 'safe'],
		]);
	}
}

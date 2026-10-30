<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\models;

use Craft;
use craft\base\Model;
use craft\elements\conditions\ElementConditionInterface;
use DateTime;
use fostercommerce\advanceddiscounts\base\DiscountTypeInterface;
use fostercommerce\advanceddiscounts\elements\conditions\CartCondition;
use fostercommerce\advanceddiscounts\elements\conditions\MessageActionRule;
use fostercommerce\advanceddiscounts\enums\TaxBasis;
use fostercommerce\advanceddiscounts\helpers\NestedConditionConfig;
use fostercommerce\advanceddiscounts\Plugin;

class Discount extends Model
{
	public ?int $id = null;

	public string $name = '';

	public bool $requireCouponCode = false;

	public bool $enabled = true;

	public bool $stopProcessing = false;

	public int $uses = 0;

	public ?int $sortOrder = null;

	public string $type = 'advanced';

	public ?TaxBasis $taxBasis = null;

	/**
	 * @var DiscountPanel[]
	 */
	public array $panels = [];

	public ?DateTime $dateCreated = null;

	public ?DateTime $dateUpdated = null;

	private ?ElementConditionInterface $_globalCartCondition = null;

	/**
	 * @var Coupon[]|null
	 */
	private ?array $_coupons = null;

	public function init(): void
	{
		parent::init();

		if ($this->panels === []) {
			$this->panels = [$this->newPanel()];
		}
	}

	public function getType(): DiscountTypeInterface
	{
		return Plugin::getInstance()->discountTypes->getDiscountTypeByHandle($this->type);
	}

	public function getGlobalCartCondition(): ElementConditionInterface
	{
		$condition = $this->_globalCartCondition ?? new CartCondition();
		$condition->mainTag = 'div';
		$condition->name = 'globalCartCondition';

		return $condition;
	}

	/**
	 * @param ElementConditionInterface|string|array<string, mixed>|null $condition
	 */
	public function setGlobalCartCondition(ElementConditionInterface|string|array|null $condition): void
	{
		$this->_globalCartCondition = NestedConditionConfig::build($condition, CartCondition::class);
	}

	/**
	 * @param array<int, array<string, mixed>> $panels
	 */
	public function setPanels(array $panels): void
	{
		if ($panels === []) {
			$this->panels = [$this->newPanel()];
			return;
		}

		$this->panels = array_map(function (array $config): DiscountPanel {
			$panel = $this->newPanel();
			$panel->name = is_string($config['name'] ?? null) ? $config['name'] : '';
			$panel->enabled = (bool) ($config['enabled'] ?? true);
			$panel->stopProcessing = (bool) ($config['stopProcessing'] ?? false);
			$panel->setCartCondition(NestedConditionConfig::extract($config, 'cartCondition'));
			$panel->setCartActionCondition(NestedConditionConfig::extract($config, 'cartActionCondition'));
			$panel->setMessageCondition(NestedConditionConfig::extract($config, 'messageCondition'));

			return $panel;
		}, array_values($panels));
	}

	/**
	 * @return Coupon[]
	 */
	public function getCoupons(): array
	{
		if ($this->_coupons === null) {
			$this->_coupons = $this->id !== null
				? Plugin::getInstance()->coupons->getCouponsByDiscountId($this->id)
				: [];
		}

		return $this->_coupons;
	}

	/**
	 * @param array<int, array<string, mixed>> $coupons
	 */
	public function setCoupons(array $coupons): void
	{
		$this->_coupons = array_values(array_filter(array_map(static function (array $config): ?Coupon {
			$code = trim(self::scalarString($config['code'] ?? null));
			if ($code === '') {
				return null;
			}

			$maxUses = trim(self::scalarString($config['maxUses'] ?? null));
			$id = $config['id'] ?? null;

			$coupon = new Coupon();
			$coupon->id = is_numeric($id) ? (int) $id : null;
			$coupon->code = $code;
			$coupon->uses = is_numeric($config['uses'] ?? null) ? (int) $config['uses'] : 0;
			$coupon->maxUses = $maxUses !== '' ? (int) $maxUses : null;

			return $coupon;
		}, $coupons)));
	}

	public function matchesCouponCode(?string $couponCode): bool
	{
		if (! $this->requireCouponCode) {
			return true;
		}

		$couponCode = trim((string) $couponCode);
		if ($couponCode === '') {
			return false;
		}

		foreach ($this->getCoupons() as $coupon) {
			if (strcasecmp((string) $coupon->code, $couponCode) === 0) {
				return $coupon->maxUses === null || $coupon->uses < $coupon->maxUses;
			}
		}

		return false;
	}

	public function validateCoupons(): void
	{
		$seenCodes = [];

		foreach ($this->getCoupons() as $coupon) {
			$normalizedCode = mb_strtolower((string) $coupon->code);
			if (isset($seenCodes[$normalizedCode])) {
				$this->addError('coupons', Craft::t('advanced-discounts', 'edit.error.couponDuplicate', [
					'code' => $coupon->code,
				]));
				continue;
			}

			$seenCodes[$normalizedCode] = true;

			if (! $coupon->validate()) {
				foreach ($coupon->getFirstErrors() as $error) {
					$this->addError('coupons', $error);
				}
			}
		}
	}

	public function validatePanels(): void
	{
		$errors = [];

		foreach ($this->panels as $panel) {
			foreach ($panel->getCartActionCondition()->getConditionRules() as $actionRule) {
				// Validate only the action values, since action rules leave the base rule's operator unset
				if ($actionRule instanceof Model && ! $actionRule->validate(['discountValue', 'buyQuantity', 'discountedQuantity'])) {
					array_push($errors, ...array_values($actionRule->getFirstErrors()));
				}
			}

			foreach ($panel->getMessageCondition()->getConditionRules() as $messageRule) {
				if ($messageRule instanceof MessageActionRule && ! $messageRule->validate(['message'])) {
					$errors[] = Craft::t('advanced-discounts', 'edit.error.messagePlaceholderUnavailable');
				}
			}
		}

		foreach (array_unique($errors) as $error) {
			$this->addError('panels', $error);
		}
	}

	/**
	 * @return array<int, mixed>
	 */
	protected function defineRules(): array
	{
		return array_merge(parent::defineRules(), [
			[['name'], 'required'],
			[['name'],
				'string',
				'max' => 255],
			[['coupons'], 'validateCoupons'],
			[['panels'], 'validatePanels'],
		]);
	}

	private static function scalarString(mixed $value): string
	{
		return is_scalar($value) ? (string) $value : '';
	}

	private function newPanel(): DiscountPanel
	{
		$panel = new DiscountPanel();
		$panel->actionConditionClass = $this->getType()::actionConditionClass();

		return $panel;
	}
}

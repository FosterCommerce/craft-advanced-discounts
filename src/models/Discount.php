<?php

namespace fostercommerce\advanceddiscounts\models;

use Craft;
use craft\base\Model;
use craft\elements\conditions\ElementConditionInterface;
use craft\helpers\Json;
use fostercommerce\advanceddiscounts\base\DiscountTypeInterface;
use fostercommerce\advanceddiscounts\elements\conditions\CartCondition;
use fostercommerce\advanceddiscounts\Plugin;

class Discount extends Model
{
	/**
	 * @var int|null ID
	 */
	public ?int $id = null;

	/**
	 * @var string Name of the discount
	 */
	public string $name = '';

	/**
	 * @var bool Whether a coupon code must be entered at checkout for this discount to apply
	 */
	public bool $requireCouponCode = false;

	/**
	 * @var bool Whether the discount is enabled
	 */
	public bool $enabled = true;

	/**
	 * @var bool Whether to stop processing further discounts once this discount matches and is applied
	 */
	public bool $stopProcessing = false;

	/**
	 * @var int Number of completed orders this discount has applied to
	 */
	public int $uses = 0;

	/**
	 * @var int|null Position of this discount relative to other discounts; lower values are evaluated first
	 */
	public ?int $sortOrder = null;

	/**
	 * @var string Handle of the discount type
	 */
	public string $type = 'advanced';

	public null|ElementConditionInterface $_globalCartCondition = null;

	/**
	 * @var DiscountPanel[]
	 */
	public array $panels = [];

	public ?\DateTime $dateCreated = null;

	public ?\DateTime $dateUpdated = null;

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
		if ($condition === null) {
			$condition = [];
		}

		if (is_string($condition)) {
			$condition = Json::decodeIfJson($condition);
		}

		if (! $condition instanceof ElementConditionInterface) {
			$condition['class'] = CartCondition::class;
			/** @phpstan-ignore-next-line */
			$condition = Craft::$app->getConditions()->createCondition($condition);
			/** @var ElementConditionInterface $condition */
		}
		$condition->forProjectConfig = false;

		$this->_globalCartCondition = $condition;
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
			$panel->name = $config['name'] ?? '';
			$panel->enabled = (bool) ($config['enabled'] ?? true);
			$panel->stopProcessing = (bool) ($config['stopProcessing'] ?? false);
			$panel->setCartCondition($config['cartCondition'] ?? []);
			$panel->setCartActionCondition($config['cartActionCondition'] ?? []);
			$panel->setMessageCondition($config['messageCondition'] ?? []);

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
	 * @param array<int, array<string, mixed>|Coupon> $coupons
	 */
	public function setCoupons(array $coupons): void
	{
		$this->_coupons = array_values(array_filter(array_map(static function (array|Coupon $config): ?Coupon {
			if ($config instanceof Coupon) {
				return $config;
			}

			$code = trim((string) ($config['code'] ?? ''));
			if ($code === '') {
				return null;
			}

			$maxUses = trim((string) ($config['maxUses'] ?? ''));

			$coupon = new Coupon();
			$coupon->id = ($config['id'] ?? null) ?: null;
			$coupon->code = $code;
			$coupon->uses = (int) ($config['uses'] ?? 0);
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
		]);
	}

	private function newPanel(): DiscountPanel
	{
		$panel = new DiscountPanel();
		$panel->actionConditionClass = $this->getType()::actionConditionClass();

		return $panel;
	}
}

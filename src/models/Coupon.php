<?php

namespace fostercommerce\advanceddiscounts\models;

use craft\base\Model;
use craft\validators\UniqueValidator;
use fostercommerce\advanceddiscounts\records\Coupon as CouponRecord;

class Coupon extends Model
{
	/**
	 * @var int|null ID
	 */
	public ?int $id = null;

	/**
	 * @var int|null ID of the discount this coupon belongs to
	 */
	public ?int $discountId = null;

	/**
	 * @var string|null The coupon code entered during checkout
	 */
	public ?string $code = null;

	/**
	 * @var int Number of times this coupon has been used
	 */
	public int $uses = 0;

	/**
	 * @var int|null Maximum number of times this coupon can be used; null for unlimited
	 */
	public ?int $maxUses = null;

	/**
	 * @return array<int, mixed>
	 */
	protected function defineRules(): array
	{
		return array_merge(parent::defineRules(), [
			[['code'], 'required'],
			[['code'],
				'string',
				'max' => 255],
			[['code'],
				UniqueValidator::class,
				'targetClass' => CouponRecord::class],
			[['uses', 'maxUses'], 'integer'],
		]);
	}
}

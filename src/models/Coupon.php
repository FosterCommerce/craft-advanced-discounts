<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\models;

use craft\base\Model;
use craft\validators\UniqueValidator;
use fostercommerce\advanceddiscounts\records\Coupon as CouponRecord;

class Coupon extends Model
{
	public ?int $id = null;

	public ?int $discountId = null;

	public ?string $code = null;

	public int $uses = 0;

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
				'targetClass' => CouponRecord::class,
				'caseInsensitive' => true],
			[['uses', 'maxUses'],
				'integer',
				'min' => 0],
		]);
	}
}

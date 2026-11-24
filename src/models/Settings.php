<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\models;

use craft\base\Model;
use fostercommerce\advanceddiscounts\enums\ShippingDiscounts;
use fostercommerce\advanceddiscounts\enums\TaxBasis;

class Settings extends Model
{
	/**
	 * @var string A string rather than the enum, since project config stores plain values
	 */
	public string $taxBasis = 'afterDiscount';

	public string $shippingDiscounts = 'shipping';

	public function getTaxBasis(): TaxBasis
	{
		return TaxBasis::tryFrom($this->taxBasis) ?? TaxBasis::AfterDiscount;
	}

	public function getShippingDiscounts(): ShippingDiscounts
	{
		return ShippingDiscounts::tryFrom($this->shippingDiscounts) ?? ShippingDiscounts::Shipping;
	}

	/**
	 * @return array<int, mixed>
	 */
	protected function defineRules(): array
	{
		return array_merge(parent::defineRules(), [
			[['taxBasis'],
				'in',
				'range' => array_column(TaxBasis::cases(), 'value')],
			[['shippingDiscounts'],
				'in',
				'range' => array_column(ShippingDiscounts::cases(), 'value')],
		]);
	}
}

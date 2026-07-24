<?php

namespace fostercommerce\advanceddiscounts\models;

use craft\base\Model;
use fostercommerce\advanceddiscounts\enums\TaxBasis;

class Settings extends Model
{
	public string $taxBasis = TaxBasis::AfterDiscount;

	/**
	 * @return array<int, mixed>
	 */
	protected function defineRules(): array
	{
		return array_merge(parent::defineRules(), [
			[['taxBasis'],
				'in',
				'range' => [TaxBasis::AfterDiscount, TaxBasis::BeforeDiscount]],
		]);
	}
}

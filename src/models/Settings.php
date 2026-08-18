<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\models;

use craft\base\Model;
use fostercommerce\advanceddiscounts\enums\TaxBasis;

class Settings extends Model
{
	/**
	 * @var string Not typed as the enum: Craft assigns plugin settings straight from project config.
	 */
	public string $taxBasis = 'afterDiscount';

	public function getTaxBasis(): TaxBasis
	{
		return TaxBasis::tryFrom($this->taxBasis) ?? TaxBasis::AfterDiscount;
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
		]);
	}
}

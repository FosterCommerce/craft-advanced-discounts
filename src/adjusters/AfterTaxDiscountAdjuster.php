<?php

namespace fostercommerce\advanceddiscounts\adjusters;

use fostercommerce\advanceddiscounts\enums\TaxBasis;

class AfterTaxDiscountAdjuster extends DiscountAdjuster
{
	protected string $servesTaxBasis = TaxBasis::BeforeDiscount;
}

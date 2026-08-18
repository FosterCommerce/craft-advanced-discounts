<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\adjusters;

use fostercommerce\advanceddiscounts\enums\TaxBasis;

class AfterTaxDiscountAdjuster extends DiscountAdjuster
{
	protected TaxBasis $servesTaxBasis = TaxBasis::BeforeDiscount;
}

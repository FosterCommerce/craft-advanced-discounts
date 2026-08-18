<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\web\assets\discounts;

use craft\web\AssetBundle;
use craft\web\assets\admintable\AdminTableAsset;
use craft\web\assets\cp\CpAsset;

class DiscountsAsset extends AssetBundle
{
	public $sourcePath = __DIR__ . '/dist';

	public $depends = [
		CpAsset::class,
		AdminTableAsset::class,
	];

	public $css = ['advanced-discounts.css'];

	public $js = ['advanced-discounts.js'];
}

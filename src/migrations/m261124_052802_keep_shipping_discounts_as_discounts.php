<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\migrations;

use Craft;
use craft\db\Migration;

/**
 * Keeps Shipping actions as discount lines on existing installs, since new installs default to reducing the shipping cost.
 */
class m261124_052802_keep_shipping_discounts_as_discounts extends Migration
{
	public function safeUp(): bool
	{
		$projectConfig = Craft::$app->getProjectConfig();

		// Apply the project config change once, not again on each environment that syncs it
		$schemaVersion = $projectConfig->get('plugins.advanced-discounts.schemaVersion', true);
		if (is_string($schemaVersion) && version_compare($schemaVersion, '1.2.0', '>=')) {
			return true;
		}

		$projectConfig->set('plugins.advanced-discounts.settings.shippingDiscounts', 'discount');

		return true;
	}
}

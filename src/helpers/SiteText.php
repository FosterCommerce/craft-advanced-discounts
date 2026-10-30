<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\helpers;

final class SiteText
{
	/**
	 * Keep the non-empty text for each site, keyed by site UID.
	 *
	 * @return array<string, string>
	 */
	public static function filter(mixed $siteTexts): array
	{
		if (! is_array($siteTexts)) {
			return [];
		}

		return array_filter($siteTexts, static fn (mixed $siteText, mixed $siteUid): bool => is_string($siteUid) && is_string($siteText) && $siteText !== '', ARRAY_FILTER_USE_BOTH);
	}
}

<?php

namespace fostercommerce\advanceddiscounts\helpers;

use craft\base\ElementInterface;
use craft\commerce\elements\Product;
use craft\commerce\elements\Variant;
use craft\commerce\Plugin as CommercePlugin;

final class Purchasables
{
	/**
	 * @return array<int, array{value: string, label: string}>
	 */
	public static function typeOptions(): array
	{
		$options = [];

		foreach ((CommercePlugin::getInstance()?->getPurchasables()->getAllPurchasableElementTypes() ?? []) as $type) {
			/** @var string|ElementInterface $type */
			$options[] = [
				'value' => $type,
				'label' => $type::displayName(),
			];
		}

		$options[] = [
			'value' => Product::class,
			'label' => Product::displayName(),
		];

		return $options;
	}

	/**
	 * @param int[] $purchasableIds
	 */
	public static function matches(ElementInterface $purchasable, string $purchasableType, array $purchasableIds): bool
	{
		$purchasableIds = array_map('intval', $purchasableIds);

		if ($purchasableType === Product::class) {
			$ownerId = $purchasable instanceof Variant ? $purchasable->getOwnerId() : null;
			return $ownerId !== null && in_array($ownerId, $purchasableIds, true);
		}

		return in_array((int) $purchasable->id, $purchasableIds, true);
	}

	/**
	 * @param int[] $purchasableIds
	 * @return int[]
	 */
	public static function expandToVariantIds(string $purchasableType, array $purchasableIds): array
	{
		if ($purchasableType === Product::class) {
			return array_map('intval', Variant::find()->productId($purchasableIds)->status(null)->ids());
		}

		return array_map('intval', $purchasableIds);
	}
}

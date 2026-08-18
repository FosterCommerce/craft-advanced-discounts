<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\helpers;

use craft\base\ElementInterface;
use craft\commerce\db\Table;
use craft\commerce\elements\Product;
use craft\commerce\elements\Variant;
use craft\commerce\Plugin as CommercePlugin;
use craft\db\Query;

final class Purchasables
{
	/**
	 * @var array<int, int[]>
	 */
	private static array $relatedVariantIds = [];

	/**
	 * @return array<int, array{value: string, label: string}>
	 */
	public static function typeOptions(): array
	{
		/** @var CommercePlugin $commerce */
		$commerce = CommercePlugin::getInstance();

		$options = [];

		foreach ($commerce->getPurchasables()->getAllPurchasableElementTypes() as $purchasableType) {
			/** @var class-string<ElementInterface> $purchasableType */
			$options[] = [
				'value' => $purchasableType,
				'label' => $purchasableType::displayName(),
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

	/**
	 * Variants a "Related To" rule covers. Stores relate an entry to the product, not to each
	 * of its variants, so a product relation has to count for every variant under it.
	 *
	 * @return int[]
	 */
	public static function relatedVariantIds(int $elementId): array
	{
		if (! isset(self::$relatedVariantIds[$elementId])) {
			$productIds = Product::find()->relatedTo($elementId)->status(null)->ids();

			self::$relatedVariantIds[$elementId] = array_values(array_unique(array_map('intval', [
				...Variant::find()->relatedTo($elementId)->status(null)->ids(),
				...($productIds === [] ? [] : Variant::find()->productId($productIds)->status(null)->ids()),
			])));
		}

		return self::$relatedVariantIds[$elementId];
	}

	/**
	 * @return int[]
	 */
	public static function nonPromotablePurchasableIds(int $storeId): array
	{
		return array_map('intval', (new Query())
			->select(['purchasableId'])
			->from(Table::PURCHASABLES_STORES)
			->where([
				'storeId' => $storeId,
				'promotable' => false,
			])
			->column());
	}
}

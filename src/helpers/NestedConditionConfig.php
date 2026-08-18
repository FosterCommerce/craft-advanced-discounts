<?php

declare(strict_types=1);

namespace fostercommerce\advanceddiscounts\helpers;

use Craft;
use craft\elements\conditions\ElementConditionInterface;
use craft\helpers\Json;

/**
 * Reads and builds the condition a condition rule nests inside itself.
 *
 * The condition builder posts a rule's nested condition under `attributes`, while a
 * stored config carries it at the top level, so both shapes reach the constructors.
 */
final class NestedConditionConfig
{
	/**
	 * @return array<string, mixed>|string|ElementConditionInterface
	 */
	public static function extract(mixed $config, string $key): array|string|ElementConditionInterface
	{
		if (! is_array($config)) {
			return [];
		}

		if (isset($config[$key])) {
			return self::normalize($config[$key]);
		}

		$attributes = $config['attributes'] ?? null;

		return is_array($attributes) ? self::normalize($attributes[$key] ?? null) : [];
	}

	/**
	 * @param array<string, mixed>|string|ElementConditionInterface|null $condition
	 * @param class-string<ElementConditionInterface> $conditionClass
	 */
	public static function build(array|string|ElementConditionInterface|null $condition, string $conditionClass): ElementConditionInterface
	{
		if (! $condition instanceof ElementConditionInterface) {
			if (is_string($condition)) {
				$decoded = Json::decodeIfJson($condition);
				$condition = is_array($decoded) ? $decoded : [];
			}

			/** @var array{class: class-string<ElementConditionInterface>} $conditionConfig */
			$conditionConfig = $condition ?? [];
			$conditionConfig['class'] = $conditionClass;

			$condition = Craft::$app->getConditions()->createCondition($conditionConfig);
		}

		$condition->forProjectConfig = false;

		return $condition;
	}

	/**
	 * @return array<string, mixed>|string|ElementConditionInterface
	 */
	private static function normalize(mixed $value): array|string|ElementConditionInterface
	{
		return is_array($value) || is_string($value) || $value instanceof ElementConditionInterface ? $value : [];
	}
}

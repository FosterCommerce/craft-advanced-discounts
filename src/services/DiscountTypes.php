<?php

namespace fostercommerce\advanceddiscounts\services;

use craft\events\RegisterComponentTypesEvent;
use fostercommerce\advanceddiscounts\base\DiscountTypeInterface;
use fostercommerce\advanceddiscounts\discounttypes\AdvancedDiscountType;
use fostercommerce\advanceddiscounts\discounttypes\BuyXGetYDiscountType;
use yii\base\Component;
use yii\base\InvalidArgumentException;

class DiscountTypes extends Component
{
	public const EVENT_REGISTER_DISCOUNT_TYPES = 'registerDiscountTypes';

	/**
	 * @return array<int, class-string<DiscountTypeInterface>>
	 */
	public function getAllDiscountTypes(): array
	{
		$event = new RegisterComponentTypesEvent([
			'types' => [
				AdvancedDiscountType::class,
				BuyXGetYDiscountType::class,
			],
		]);
		$this->trigger(self::EVENT_REGISTER_DISCOUNT_TYPES, $event);

		/** @var array<int, class-string<DiscountTypeInterface>> $types */
		$types = $event->types;

		return $types;
	}

	/**
	 * @return DiscountTypeInterface[]
	 */
	public function getAllDiscountTypeInstances(): array
	{
		return array_map(static fn (string $type): DiscountTypeInterface => new $type(), $this->getAllDiscountTypes());
	}

	public function getDiscountTypeByHandle(string $handle): DiscountTypeInterface
	{
		foreach ($this->getAllDiscountTypes() as $type) {
			if ($type::handle() === $handle) {
				return new $type();
			}
		}

		throw new InvalidArgumentException("No discount type exists with handle \"{$handle}\".");
	}
}

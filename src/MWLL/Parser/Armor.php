<?php

namespace MWLL\Parser;

use Symfony\Component\VarDumper\VarDumper;

class Armor
{
	/**
	 * Prevent direct instantiation (Singleton)
	 */
	protected function __construct($strArmorPath)
	{
		// check if file was given
		if (!$strArmorPath)
		{
			throw new \RuntimeException('No lua path given.');
		}

		// check if file exists
		if (!file_exists($strArmorPath))
		{
			throw new \RuntimeException($strArmorPath . ' does not exist.');
		}

		$this->strContent = file_get_contents($strArmorPath);
	}


	/**
	 * Prevent cloning of the object (Singleton)
	 */
	final public function __clone() {}


	/**
	 * Initiates the singleton
	 *
	 * @return Prices The object instance
	 */
	public static function init($strArmorPath)
	{
		if (static::$objInstance === null)
		{
			static::$objInstance = new static($strArmorPath);
		}

		return static::$objInstance;
	}


	/**
	 * Return the current object instance (Singleton)
	 *
	 * @return Prices The object instance
	 */
	public static function getInstance()
	{
		if (static::$objInstance === null)
		{
			throw new \RuntimeException('Armor has not been initialized.');
		}

		return static::$objInstance;
	}
}

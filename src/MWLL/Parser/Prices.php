<?php

namespace MWLL\Parser;

class Prices
{
	/**
	 * Object instance (Singleton)
	 * @var Prices
	 */
	protected static $objInstance;

	/**
	 * Price table cache
	 * @var array
	 */
	protected $arrPrices;

	/**
	 * The lua file content
	 * @var string
	 */
	protected $strContent;


	/**
	 * Prevent direct instantiation (Singleton)
	 */
	protected function __construct($strLuaPath)
	{
		// check if file was given
		if (!$strLuaPath)
		{
			throw new \RuntimeException('No lua path given.');
		}

		// check if file exists
		if (!file_exists($strLuaPath))
		{
			throw new \RuntimeException($strLuaPath . ' does not exist.');
		}

		$this->strContent = file_get_contents($strLuaPath);
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
	public static function init($strLuaPath)
	{
		if (static::$objInstance === null)
		{
			static::$objInstance = new static($strLuaPath);
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
			throw new \RuntimeException('Prices have not been initialized.');
		}

		return static::$objInstance;
	}


	/**
	 * Static helper function for getPrice
	 *
	 * @param string $class
	 * @param string $modification
	 *
	 * @return integer
	 */
	public static function price($class, $modification = null)
	{
		// get the object instance
		$objInstance = self::getInstance();

		// return the price
		return $objInstance->getPrice($class, $modification);
	}


	/**
	 * Returns an asset price for the given class and optionally modification
	 *
	 * @param string $class
	 * @param string $modification
	 *
	 * @return integer
	 */
	public function getPrice($class, $modification = null)
	{
		// ignore base modification
		if ('Base' == $modification)
		{
			return 0;
		}

		$key = $class . ($modification ?: ''); 

		if (isset($this->arrPrices[$key]))
		{
			return $this->arrPrices[$key];
		}

		// check if class exists at all
		if (strpos($this->strContent, '"'.$class.'"') === false)
		{
			trigger_error('Could not find price for "'.$key.'".', E_USER_WARNING);
			return 0;
		}

		// search for class
		$pattern = '/[^-]{(?:(?!}).)*class="'.$class.'".*?}/ms';
		if ($modification)
		{
			$pattern = '/[^-]{(?:(?!}).)*class="'.$class.'"(?:(?!}).)*modification="'.$modification.'".*?}/ms';
		}

		if (preg_match($pattern, $this->strContent, $matches))
		{
			if (preg_match('/price=([0-9]+)/', $matches[0], $matches))
			{
				$this->arrPrices[$key] = (int)$matches[1];
				return $this->arrPrices[$key];
			}
		}

		// search for id
		$pattern = '/[^-]{(?:(?!}).)*id="'.$class.'".*?}/ms';

		if (preg_match($pattern, $this->strContent, $matches))
		{
			if (preg_match('/price=([0-9]+)/', $matches[0], $matches))
			{
				$this->arrPrices[$key] = (int)$matches[1];
				return $this->arrPrices[$key];
			}
		}

		throw new \RuntimeException('Could not find price for "'.$key.'".');
	}


	/**
	 * Returns the cached price table
	 *
	 * @return array
	 */
	public function getCachedPriceTable()
	{
		$arrPrices = $this->arrPrices;
		ksort($arrPrices);
		return $arrPrices;
	}
}

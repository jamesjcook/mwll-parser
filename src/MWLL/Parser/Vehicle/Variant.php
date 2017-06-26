<?php

namespace MWLL\Parser\Vehicle;

use MWLL\Parser\Prices;
use Symfony\Component\VarDumper\VarDumper;

class Variant
{
	/**
	 * XML object
	 * @var \SimpleXMLElement
	 */
	protected $objXml;

	/**
	 * Variant name
	 * @var string
	 */
	protected $strName;

	/**
	 * Weapons array
	 * @var array
	 */
	protected $intArmor = 0;

	/**
	 * Weapons array
	 * @var array
	 */
	protected $arrWeapons = array();

	/**
	 * Equipment array
	 * @var array
	 */
	protected $arrEquipment = array();

	/**
	 * Price
	 * @var array
	 */
	protected $intBasePrice;

	/**
	 * Price
	 * @var array
	 */
	protected $intTotalPrice;

	/**
	 * Equipment array
	 * @var array
	 */
	protected static $arrEquipmentAssets = array('EnhancedOptics','AntiMissileSystem');

	/**
	 * Ignore array
	 * @var array
	 */
	protected static $arrIgnoreAssets = array('StandardOptics');


	/**
	 * Constructor
	 *
	 * @param string $strVehicleName
	 * @param \SimpleXMLElement $objXml
	 * @param Variant $objBaseVariant
	 *
	 * @return void
	 */
	public function __construct($strVehicleName, \SimpleXMLElement $objXml, Variant $objBaseVariant = null)
	{
		// save the XML
		$this->objXml = $objXml;

		// save the name
		$this->strName = (string)$objXml['name'];

		// go through each asset of the variant
		foreach ($objXml->Elems->Elem as $asset)
		{
			// get the asset's value and name
			$name = (string)$asset['name'];
			$value = (string)$asset['value'];

			// check for value
			if (!$value)
			{
				continue;
			}

			// ignore some assets
			if (in_array($value, self::$arrIgnoreAssets))
			{
				continue;
			}

			// equipment
			if ($name == 'type' || in_array($value,  self::$arrEquipmentAssets))
			{
				if (isset($this->arrEquipment[$value]))
				{
					++$this->arrEquipment[$value];
				}
				else
				{
					$this->arrEquipment[$value] = 1;
				}
			}
			// weapon
			elseif ($name == 'class')
			{
				if (isset($this->arrWeapons[$value]))
				{
					++$this->arrWeapons[$value];
				}
				else
				{
					$this->arrWeapons[$value] = 1;
				}
			}
			// armor
			elseif ($name == 'damageMax')
			{
				$this->intArmor += $value;
			}
		}

		// get the base price for this variant
		$this->intBasePrice = Prices::price($strVehicleName, $this->strName);

		// calculate the total price
		$this->intTotalPrice = $this->intBasePrice;
		foreach ($this->arrWeapons as $strClass => $count)
		{
			$this->intTotalPrice += Prices::price($strClass) * $count;
		}
		foreach ($this->arrEquipment as $strClass => $count)
		{
			$this->intTotalPrice += Prices::price($strClass);
		}
		$this->intTotalPrice += Prices::price('damageMax') * $this->intArmor;
	}


	/**
	 * Getter for name
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->strName;
	}
}

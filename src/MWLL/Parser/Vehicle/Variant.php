<?php

namespace MWLL\Parser\Vehicle;

use MWLL\Parser\Prices;
use Symfony\Component\VarDumper\VarDumper;

class Variant
{
	/**
	 * Variant name
	 * @var string
	 */
	protected $strName;

	/**
	 * Armor
	 * @var integer
	 */
	protected $intArmor = 0;

	/**
     * Armor tonnage
     * @var float
     */
	protected $floatArmorTonnage;

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
	 * Free tons
	 * @var array
	 */
	protected $intFreeTons = 0;

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
	 * @param \SimpleXMLElement $objVariant
	 * @param Variant $objBaseVariant
	 *
	 * @return void
	 */
	public function __construct($strVehicleName, \SimpleXMLElement &$objVariant, \SimpleXMLElement &$objRoot, Variant &$objBaseVariant = null)
	{
		// save the name
		$this->strName = (string)$objVariant['name'];

		// go through each asset of the variant
		foreach ($objVariant->Elems->Elem as $asset)
		{
			// get the asset's value and name
			$name = (string)$asset['name'];
			$value = (string)$asset['value'];
			$idref = (string)$asset['idRef'];

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

			// free tons
			if ($idref == 'idRemainingTonnage')
			{
				$this->intFreeTons = (int)$value;
			}
			// equipment
			elseif ($name == 'type' || in_array($value,  self::$arrEquipmentAssets))
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
			// heatsinks
			elseif ($name == 'heatsinkCount')
			{
				$this->arrEquipment['HS'] = (int)$value;
			}
		}

		// convert to double heatsinks if applicable
		if (isset($this->arrEquipment['HS']))
		{
			foreach ($objVariant->Elems->Elem as $asset)
			{
				// get the asset's value and name
				$name = (string)$asset['name'];
				$value = (string)$asset['value'];

				if ($name == 'hasDoubleHeatsinks' && $value == '1')
				{
					$count = $this->arrEquipment['HS'];
					unset($this->arrEquipment['HS']);
					$this->arrEquipment['DHS'] = $count;
					break;
				}
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

		/**
		 * The correlation between armor values and armor tonnage seems broken,
		 * so I am assuming the armor values of the Owens equal to a tonnage
		 * of 7, which results in this factor. Also I am rounding to half integers.
		 */
		$this->floatArmorTonnage = round($this->intArmor * 0.0002054171435278927 * 2) * 0.5;
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

<?php

namespace MWLL\Parser\Vehicle;

use MWLL\Parser\Prices;
use MWLL\Parser\Parser;
use MWLL\Parser\Vehicle\Vehicle;
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
	 * Max speed
	 * @var float
	 */
	protected $floatSpeed;

	/**
	 * Equipment array
	 * @var array
	 */
	protected static $arrEquipmentAssets = array('EnhancedOptics','AntiMissileSystem');

	/**
	 * Ignore array
	 * @var array
	 */
	protected static $arrIgnoreAssets = array('StandardOptics','LongRangeRadar','Radar');


	/**
	 * Constructor
	 *
	 * @param string $strVehicleName
	 * @param \SimpleXMLElement $objVariant
	 * @param Variant $objBaseVariant
	 *
	 * @return void
	 */
	public function __construct(Vehicle $objVehicle, \SimpleXMLElement &$objVariant, \SimpleXMLElement &$objRoot, Variant &$objBaseVariant = null)
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
			// speed
			elseif ($name == 'actualMaxSpeed')
			{
				$this->floatSpeed = (float)$value;
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

		// sort
		ksort($this->arrWeapons);
		ksort($this->arrEquipment);

		// get the base price for this variant
		$this->intBasePrice = Prices::price($objVehicle->getName(), $this->strName);

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
		 * Calculate armor tonnage
		 *
		 * The correlation between armor values and armor tonnage seems broken,
		 * so I am assuming the armor values of certain assets being a certain
		 * tonnage, which results in these factors. Also I am rounding to half integers.
		 */
		if ($objVehicle->getType() == 'Tank' || $objVehicle->getType() == 'Wheeled')
		{
			// here the Demolisher Prime is assumed to have 11.5t of armor
			$this->floatArmorTonnage = round($this->intArmor * 0.0002379966887417219 * 2) * 0.5;
		}
		elseif ($objVehicle->getType() == 'Aerospace')
		{
			// here the Sparrow Hawk Prime is assumed to have 6t of armor
			$this->floatArmorTonnage = round($this->intArmor * 0.0004285714285714286 * 2) * 0.5;	
		}
		else
		{
			// here the Owens Prime is assumed to have 7t of armor
			$this->floatArmorTonnage = round($this->intArmor * 0.0002054171435278927 * 2) * 0.5;
		}
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->strName;
	}

	/**
	 * @return array
	 */
	public function getWeapons()
	{
		return $this->arrWeapons;
	}

	/**
	 * Returns the equipment of the variant.
	 *
	 * @param array $arrFilter Optional list of equipments to filter
	 * @param boolean $blnInverse Inverse the filter logic
	 *
	 * @return array
	 */
	public function getEquipment($arrFilter = null, $blnInverse = false)
	{
		if ($arrFilter)
		{
			if ($blnInverse)
			{
				return array_diff_key($this->arrEquipment, array_flip($arrFilter));
			}

			return array_intersect_key($this->arrEquipment, array_flip($arrFilter));
		}

		return $this->arrEquipment;
	}

	/**
	 * @return float
	 */
	public function getArmorTonnage()
	{
		return $this->floatArmorTonnage;
	}

	/**
	 * @return boolean
	 */
	public function hasMasc()
	{
		return in_array('MASC', array_keys($this->arrEquipment));
	}

	/**
	 * @return float
	 */
	public function getSpeed()
	{
		return $this->floatSpeed ?: $this->objVehicle->getSpeed();
	}

	/**
	 * @return float
	 */
	public function getMascSpeed()
	{
		return $this->getSpeed() * 1.4;
	}
}

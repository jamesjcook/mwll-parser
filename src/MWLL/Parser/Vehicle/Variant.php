<?php

namespace MWLL\Parser\Vehicle;

use Symfony\Component\VarDumper\VarDumper;

class Variant
{
	/**
	 * XML object
	 * @var \SimpleXMLElement
	 */
	private $objXml;

	/**
	 * Variant name
	 * @var string
	 */
	private $strName;

	/**
	 * Weapons array
	 * @var array
	 */
	private $intArmor = 0;

	/**
	 * Weapons array
	 * @var array
	 */
	private $arrWeapons = array();

	/**
	 * Equipment array
	 * @var array
	 */
	private $arrEquipment = array();


	/**
	 * Constructor
	 *
	 * @param \SimpleXMLElement $objXml
	 *
	 * @return void
	 */
	public function __construct(\SimpleXMLElement $objXml, Variant $objBaseVariant = null)
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
			if (in_array($value, array('StandardOptics')))
			{
				continue;
			}

			// equipment
			if ($name == 'type' || in_array($value, array('EnhancedOptics','StandardOptics')))
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
	}
}

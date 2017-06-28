<?php

namespace MWLL\Parser\Vehicle;

use MWLL\Parser\Vehicle\Variant;
use Symfony\Component\VarDumper\VarDumper;

class Vehicle
{
	/**
	 * Optional base variant
	 * @var Variant
	 */
	protected $objBaseVariant = null;

	/**
	 * All variants
	 * @var array
	 */
	protected $arrVariants = array();

	/**
	 * Name of the vehicle
	 * @var string
	 */
	protected $strName;

	/**
	 * Whether this vehicle has XL engines
	 * @var boolean
	 */
	protected $blnHasXL = false;

	/**
	 * Tech
	 * @var string
	 */
	protected $strTech;

	/**
	 * Tonnage
	 * @var integer
	 */
	protected $intTonnage;

	/**
	 * Type
	 * @var string
	 */
	protected $strType;

	/**
	 * Max speed
	 * @var float
	 */
	protected $intMaxSpeed;

	/**
	 * Vehicle types
	 * @var array
	 */
	protected static $arrVehicleTypes = array('Mech','Tank','Aerospace');


	/**
	 * Constructor for Vehicle.
	 *
	 * @param string $strXmlPath The path to the XML file of the vehicle.
	 *
	 * @return void
	 */
	public function __construct($strXmlPath)
	{
		// load the XML
		$objXml = simplexml_load_file($strXmlPath);

		// check if successful
		if ($objXml === false)
		{
			throw new \RuntimeException('Could not load '.$strXmlPath);
		}

		// save the name
		$this->strName = (string)$objXml['name'];

		// determine the tech
		$arrName = explode('_', $this->strName);
		if ($arrName)
		{
			$this->strTech = $arrName[0];
		}

		// determine the tonnage and type
		foreach (self::$arrVehicleTypes as $strType)
		{
			if (isset($objXml->MovementParams[0]->{$strType}))
			{
				$this->intTonnage = (int)$objXml->MovementParams[0]->{$strType}['tonnage'];
				$this->strType = $strType;
				if (isset($objXml->MovementParams[0]->{$strType}['actualMaxSpeed']))
				{
					$this->intMaxSpeed = (int)$objXml->MovementParams[0]->{$strType}['actualMaxSpeed'];
				}
				break;
			}
		}

		// check for XL engine
		if (isset($objXml->MovementParams[0]->Mech->Components))
		{
			foreach ($objXml->MovementParams[0]->Mech->Components->Component as $component)
			{
				if ($component['name'] == 'lefttorso')
				{
					foreach ($component->ProxyTransferDamages as $damage)
					{
						if ($damage['target'] == 'centertorso')
						{
							if ($damage['transferRatio'] > 1.0)
							{
								$this->blnHasXL = true;
							}
							break;
						}
					}
					break;
				}
			}
		}

		// check for base variant
		foreach ($objXml->Modifications->Modification as $modification)
		{
			if ('Base' == $modification['name'])
			{
				$this->objBaseVariant = new Variant($this->strName, $modification, $objXml);
				break;
			}
		}

		// load all variants
		foreach ($objXml->Modifications->Modification as $modification)
		{
			if ('Base' != $modification['name'])
			{
				$objVariant = new Variant($this->strName, $modification, $objXml, $this->objBaseVariant);
				$this->arrVariants[$objVariant->getName()] = $objVariant;
			}
		}
	}


	/**
	 * Getter for variants
	 *
	 * @return array
	 */
	public function getVariants()
	{
		return $this->arrVariants;
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

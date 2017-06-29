<?php

namespace MWLL\Parser\Vehicle;

use MWLL\Parser\Vehicle\Variant;
use MWLL\Parser\Parser;
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
	protected $floatSpeed;


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
		foreach (Parser::getVehicleTypes() as $strType)
		{
			if (isset($objXml->MovementParams[0]->{$strType}))
			{
				$this->intTonnage = (int)$objXml->MovementParams[0]->{$strType}['tonnage'];
				$this->strType = $strType;
				if (isset($objXml->MovementParams[0]->{$strType}['maxSpeed']))
				{
					// factor suggested by invictus
					$this->floatSpeed = floatval($objXml->MovementParams[0]->{$strType}['maxSpeed']) * 3.56;
				}
				if (isset($objXml->MovementParams[0]->{$strType}['actualMaxSpeed']))
				{
					// this is the observed in-game speed
					$this->floatSpeed = floatval($objXml->MovementParams[0]->{$strType}['actualMaxSpeed']);
				}
				break;
			}
		}

		// check for XL engine
		if (isset($objXml->MovementParams[0]->Mech->Components))
		{
			foreach ($objXml->MovementParams[0]->Mech->Components->Component as $component)
			{
				if ((string)$component['name'] == 'lefttorso')
				{
					foreach ($component->ProxyTransferDamages as $damage)
					{
						if ((string)$damage['target'] == 'centertorso')
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
				$this->objBaseVariant = new Variant($this, $modification, $objXml);
				break;
			}
		}

		// load all variants
		foreach ($objXml->Modifications->Modification as $modification)
		{
			if ('Base' != $modification['name'])
			{
				$objVariant = new Variant($this, $modification, $objXml, $this->objBaseVariant);
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

	/** 
	 * @return string
	 */
	public function getCommonName()
	{
		$arrName = explode('_',$this->strName);
		if (count($arrName) > 1)
		{
			return $arrName[1];
		}
		else
		{
			return $this->strName;
		}
	}

	/**
	 * @return float
	 */
	public function getSpeed()
	{
		return $this->floatSpeed;
	}

	/**
	 * @return float
	 */
	public function getMascSpeed()
	{
		return $this->floatSpeed * 1.4;
	}

	/**
	 * @return string
	 */
	public function getTech()
	{
		return $this->strTech;
	}

	/**
	 * @return string
	 */
	public function getClass()
	{
		if ($this->intTonnage > 75 && $this->strType != 'Aerospace')
		{
			return 'Assault';
		}
		elseif ($this->intTonnage > 55)
		{
			return 'Heavy';
		}
		elseif ($this->intTonnage > 35)
		{
			return 'Medium';
		}
		else
		{
			return 'Light';
		}
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->strType;
	}

	/**
	 * @return integer
	 */
	public function getTonnage()
	{
		return $this->intTonnage;
	}
}

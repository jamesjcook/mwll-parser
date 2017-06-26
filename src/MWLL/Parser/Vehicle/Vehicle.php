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

		if ($this->strName == 'IS_Atlas_Mech')
		{
			//VarDumper::dump($objXml); exit;
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
						if ($damage['traget'] == 'centertorso')
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

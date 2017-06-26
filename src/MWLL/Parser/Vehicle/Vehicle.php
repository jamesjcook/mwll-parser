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
	private $objBaseVariant = null;

	/**
	 * All variants
	 * @var array
	 */
	private $arrVariants = array();

	/**
	 * Name of the vehicle
	 * @var string
	 */
	private $strName;


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

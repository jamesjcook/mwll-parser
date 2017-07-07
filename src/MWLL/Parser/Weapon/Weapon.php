<?php

namespace MWLL\Parser\Weapon;

use MWLL\Parser\Parser;
use Symfony\Component\VarDumper\VarDumper;

class Weapon
{
	/**
	 * Internal identifier
	 * @var string
	 */
	protected $strName;

	/**
	 * Weapon name (HUD)
	 * @var string
	 */
	protected $strWeaponName;

	/**
	 * Weapon range (HUD)
	 * @var integer
	 */
	protected $intRange;


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
		if (isset($objXml['name']))
		{
			$this->strName = (string)$objXml['name'];
		}

		// parse weapon params
		if (isset($objXml->params->param))
		{
			foreach ($objXml->params->param as $param)
			{
				$name = (string)$param['name'];
				$value = (string)$param['value'];
				
				if ($name == 'range')
				{
					$this->intRange = (int)$value;
				}
			}
		}

		// parse weapon group data
		if (isset($objXml->weaponGroupData->param))
		{
			foreach ($objXml->weaponGroupData->param as $param)
			{
				$name = (string)$param['name'];
				$value = (string)$param['value'];

				if ($name == 'weaponName')
				{
					$this->strWeaponName = $value;
				}
			}
		}
	}


	/**
	 * Returns internal identifier
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->strName;
	}

	/**
	 * Returns the weapon name (HUD)
	 *
	 * @return string
	 */
	public function getWeaponName()
	{
		return $this->strWeaponName ?: $this->strName;
	}

	/**
	 * @return integer
	 */
	public function getRange()
	{
		return $this->intRange;
	}
}

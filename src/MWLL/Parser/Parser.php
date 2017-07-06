<?php

namespace MWLL\Parser;

use MWLL\Parser\Vehicle\Vehicle;
use MWLL\Parser\Weapon\Weapon;
use MWLL\Parser\Equipment\Equipment;
use Symfony\Component\VarDumper\VarDumper;

class Parser
{
	/**
	 * Vehicle types
	 * @var array
	 */
	protected static $arrVehicleTypes = array('Mech','Tank','Aerospace','STOVL','LightVTOL','Hovercraft','StdWheeled');

	/**
	 * Vehicle types
	 * @var array
	 */
	protected static $arrIgnoreVehicles = array('IS_Karnov');

	/**
	 * Vehicles
	 * @var array
	 */
	protected static $arrVehicles = array();

	/**
	 * Weapons
	 * @var array
	 */
	protected static $arrWeapons = array();


	/**
	 * Parses the extracted GameData folder
	 * and returns an array of Vehicle objects.
	 *
	 * @param string $strFolder The path to the extracted GameData folder
	 *
	 * @return array An array of MWLL\Parser\Vehicle objects
	 */
	public static function parse($strGameDataFolder)
	{
		// define the path to the Scripts\GameRules\MechLists.lua
		$strMechListsPath = $strGameDataFolder . '/Scripts/GameRules/MechLists.lua';

		// generate the prices instance
		Prices::init($strMechListsPath);

		// define the folder to the vehicle XMLs
		$strVehicleFolder = $strGameDataFolder . '/Scripts/Entities/Vehicles/Implementations/Xml';

		// check if folder exists
		if (!file_exists($strVehicleFolder))
		{
			throw new \RuntimeException('Folder '. $strVehicleFolder . ' does not exist.');
		}

		// clear
		self::$arrVehicles = array();

		// go through each XML
		foreach (new \DirectoryIterator($strVehicleFolder) as $fileInfo)
		{
			if ($fileInfo->getExtension() == 'xml')
			{
				$objVehicle = new Vehicle($fileInfo->getPathname());
				if (!in_array($objVehicle->getName(), self::$arrIgnoreVehicles))
				{		
					self::$arrVehicles[$objVehicle->getName()] = $objVehicle;
				}
			}
		}

		// sort
		ksort(self::$arrVehicles);

		// define the folder to the weapon XMLs
		$strWeaponFolder = $strGameDataFolder . '/Scripts/Entities/Items/XML/Weapons/Vehicles';

		// check if folder exists
		if (!file_exists($strWeaponFolder))
		{
			throw new \RuntimeException('Folder '. $strWeaponFolder . ' does not exist.');
		}

		// clear
		self::$arrWeapons = array();

		// go through each XML
		foreach (new \DirectoryIterator($strWeaponFolder) as $fileInfo)
		{
			if ($fileInfo->getExtension() == 'xml')
			{
				$objWeapon = new Weapon($fileInfo->getPathname());
				self::$arrWeapons[$objWeapon->getName()] = $objWeapon;
			}
		}

		// sort
		ksort(self::$arrWeapons);
	}


	/**
	 * Returns the vehicle types
	 *
	 * @return array
	 */
	public static function getVehicleTypes()
	{
		return self::$arrVehicleTypes;
	}


	/**
	 * Returns all parsed vehicles
	 *
	 * @return array
	 */
	public static function getVehicles()
	{
		return self::$arrVehicles;
	}


	/**
	 * Returns all parsed weapons
	 *
	 * @return array
	 */
	public static function getWeapons()
	{
		return self::$arrWeapons;
	}
}

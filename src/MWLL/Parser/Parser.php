<?php

namespace MWLL\Parser;

use MWLL\Parser\Vehicle\Vehicle;
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
	protected static $arrEquipmentMapping = array(
		'C3Radar' => 'Radar',
		'C3LongRangeRadar' => 'C3LongRangeRadar',
		'BAP' => 'Radar',
		'BHP' => 'Radar',
		'GECM' => 'Radar',
		'AECM' => 'Radar',
		'AirGECM' => 'Radar',
		'EnhancedOptics' => 'Equipment',
		'HS' => 'Equipment',
		'DHS' => 'Equipment',
		'TAG' => 'Equipment',
		'AntiMissileSystem' => 'Equipment',
		'ImprLightMechJumpJets' => 'Movement',
		'ImprMediumMechJumpJets' => 'Movement',
		'ImprHeavyMechJumpJets' => 'Movement',
		'ImprAssaultMechJumpJets' => 'Movement',
		'StndLightMechJumpJets' => 'Movement',
		'StndMediumMechJumpJets' => 'Movement',
		'StndHeavyMechJumpJets' => 'Movement',
		'StndAssaultMechJumpJets' => 'Movement',
		'MASC' => 'Movement',
	);


	/**
	 * Parses the extracted GameData folder
	 * and returns an array of Vehicle objects.
	 *
	 * @param string $strFolder The path to the extracted GameData folder
	 *
	 * @return array An array of MWLL\Parser\Vehicle objects
	 */
	public static function parseVehicles($strGameDataFolder)
	{
		// define the folder to the vehicle XMLs
		$strVehicleFolder = $strGameDataFolder . '/Scripts/Entities/Vehicles/Implementations/Xml';

		// check if folder exists
		if (!file_exists($strVehicleFolder))
		{
			throw new \RuntimeException('Folder '. $strVehicleFolder . ' does not exist.');
		}

		// define the path to the Scripts\GameRules\MechLists.lua
		$strMechListsPath = $strGameDataFolder . '/Scripts/GameRules/MechLists.lua';

		// generate the prices instance
		Prices::init($strMechListsPath);

		// prepare result
		$arrVehicles = array();

		// go through each XML
		foreach (new \DirectoryIterator($strVehicleFolder) as $fileInfo)
		{
			if ($fileInfo->getExtension() == 'xml')
			{
				$objVehicle = new Vehicle($fileInfo->getPathname());
				$arrVehicles[$objVehicle->getName()] = $objVehicle;
			}
		}

		// sort
		ksort($arrVehicles);

		// return the vehicles
		return $arrVehicles;
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
	 * Returns the equipment mapping
	 *
	 * @return array
	 */
	public static function getEquipmentMapping()
	{
		return self::$arrEquipmentMapping;
	}
}

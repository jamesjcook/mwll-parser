<?php

namespace MWLL\Parser;

use MWLL\Parser\Vehicle\Vehicle;
use LUAParser;

use Symfony\Component\VarDumper\VarDumper;

class Parser
{
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

		// check if file exists
		if (!file_exists($strMechListsPath))
		{
			throw new \RuntimeException($strMechListsPath . ' does not exist.');
		}

		// parse LUA
		//$objLuaParser = new LUAParser();
		//$objLuaParser->parseFile($strMechListsPath);
		$strLua = file_get_contents($strMechListsPath);
		$strLua = str_replace('function AddDataLists(gm)', '', $strLua);
		$strLua = implode("\n", array_slice(explode("\n", $strLua), 0, 1016));
		$strLua = str_replace('gm.', 'gm_', $strLua);
		VarDumper::dump(parse_lua($strLua)); exit;


		//VarDumper::dump(parse_lua($strLua));

		// prepare result
		$arrVehicles = array();

		// go through each XML
		foreach (new \DirectoryIterator($strVehicleFolder) as $fileInfo)
		{
			if ($fileInfo->getExtension() == 'xml')
			{
				$arrVehicles[] = new Vehicle($fileInfo->getPathname());
			}
		}

		// return the vehicles
		return $arrVehicles;
	}
}

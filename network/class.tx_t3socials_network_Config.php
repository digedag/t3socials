<?php
/***************************************************************
*  Copyright notice
*
 * (c) 2014 DMK E-BUSINESS GmbH <kontakt@dmk-ebusiness.de>
 * All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
require_once t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php');

/**
 *
 * @package tx_t3socials
 * @subpackage tx_t3socials_network
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class tx_t3socials_network_Config {

	private static $networks = array();

	/**
	 *
	 * @param string $connectionClass
	 */
	public static function registerNetwork($connectionClass) {
		$id = strtolower($id);
		if (!tx_rnbase::load($connectionClass)) {
			throw new Exception('Could not load network connection: ' . $connectionClass);
		}

		$con = tx_rnbase::makeInstance($connectionClass);
		if (!$con instanceof tx_t3socials_network_IConnection) {
			throw new Exception('The connection "'.get_class($con).'" has to implement the interface "tx_t3socials_network_IConnection".');
		}

		$config = $con->getNetworkConfig();
		$id = $config->getProviderId();

		self::$networks[$id] = $config;
	}

	/**
	 * @return array
	 */
	public static function getNewtorkIds() {
		return array_keys(self::$networks);
	}

	/**
	 *
	 * @param unknown_type $network
	 *
	 * @throws Exception
	 *
	 * @return tx_t3socials_models_NetworkConfig
	 */
	public static function getNetworkConfig($network) {
		$id = $network instanceof tx_t3socials_models_Network
			? $network->getNetwork() : $network;
		if (!isset(self::$networks[$id])) {
			throw new Exception('Unknown network type: ' . $id);
		}
		return self::$networks[$id];
	}

	/**
	 * @return array
	 */
	public static function getNewtorkComunicators() {
		$return = array();
		/* @var $config tx_t3socials_models_NetworkConfig */
		foreach (self::$networks as $config) {
			$class = $config->getComunicatorClass();
			if (!$class) {
				continue;
			}
			$comunicator = tx_rnbase::makeInstance($class);
			if (!$comunicator instanceof tx_rnbase_mod_IModHandler) {
				throw new Exception('The $comunicator "'.get_class($comunicator).'" has to implement the interface "tx_rnbase_mod_IModHandler".');
			}
			$return[] = $comunicator;
		}
		return $return;
	}

	/**
	 *
	 * @param string|tx_t3socials_models_Network $network
	 *
	 * @throws Exception
	 *
	 * @return tx_t3socials_network_IConnection
	 */
	public static function getNetworkConnection($network) {
		if ($network instanceof tx_t3socials_models_Network) {
			$class = $network->getConfigData($network->getNetwork().'.connection');
			if ($class) {
				$con = tx_rnbase::makeInstance($class);
				if (!$con instanceof tx_t3socials_network_IConnection) {
					throw new Exception('The connection "'.get_class($con).'" has to implement the interface "tx_t3socials_network_IConnection".');
				}
				return $con;
			}
		}
		$config = self::getNetworkConfig($network);
		$class = $config->getConnectorClass();
		if (!tx_rnbase::load($class)) {
			throw new Exception('Could not load network connection: ' . $class);
		}
		$con = tx_rnbase::makeInstance($class);
		if (!$con instanceof tx_t3socials_network_IConnection) {
			throw new Exception('The connection "'.get_class($con).'" has to implement the interface "tx_t3socials_network_IConnection".');
		}
		if ($network instanceof tx_t3socials_models_Network) {
			$con->setNetwork($network);
		}
		return $con;
	}


	/**
	 * @param string|tx_t3socials_models_Network $network
	 *
	 * @return string
	 */
	public static function translateNetwork($network) {
		$id = $network instanceof tx_t3socials_models_Network
			? $network->getNetwork() : $network;
		tx_rnbase::load('tx_rnbase_util_Misc');
		$title = tx_rnbase_util_Misc::translateLLL('LLL:EXT:t3socials/Resources/Private/Language/locallang_db.xml:tx_t3socials_'.$id);
		return empty($title) ? $id : $title;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3socials/network/class.tx_t3socials_network_HybridAuth.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3socials/network/class.tx_t3socials_network_HybridAuth.php']);
}
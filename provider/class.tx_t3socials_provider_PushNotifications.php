<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Rene Nitzsche
 *  Contact: rene@system25.de
 *  All rights reserved
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 ***************************************************************/

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

tx_rnbase::load('tx_t3rest_models_Provider');
tx_rnbase::load('tx_t3rest_provider_AbstractBase');
tx_rnbase::load('tx_t3rest_util_Objects');
tx_rnbase::load('tx_rnbase_filter_BaseFilter');
tx_rnbase::load('tx_rnbase_util_Logger');



/**
 * This is a REST provider for T3socials pushd network
 * UseCases:
 * get = teamUid -> return a specific team
 * getdefined = cfc1 -> return a specific preconfigured team
 *
 * @author Rene Nitzsche
 */
class tx_t3socials_provider_PushNotifications extends tx_t3rest_provider_AbstractBase {

	protected function handleRequest($configurations, $confId) {
		if($tableAlias = $configurations->getParameters()->get('get')) {
			$data = $this->getNetwork($tableAlias, $configurations, $confId.'get.');
		}
		return $data;
	}

	protected function getConfId() {
		return 't3socials.pushd.';
	}
	protected function getBaseClass() {
		return 'tx_t3socials_models_Network';
	}

	/**
	 * Lädt einen Account
	 *
	 * @param string $tableAlias string-Identifier
	 * @return tx_t3socials_model_Network
	 */
	private function getNetwork($tableAlias, $configurations, $confId) {

		$ret = false;
		// Prüfen, ob der Dienst konfiguriert ist
		$defined = $configurations->getKeyNames($confId.'defined.');
		if(in_array($tableAlias, $defined)) {
			$ret = new stdClass();
			$itemId = $configurations->get($confId.'defined.'.$tableAlias.'.network');
			$account = tx_rnbase::makeInstance('tx_t3socials_models_Network', $itemId);
			$ret = $this->getEvents($account);
		}
		return $ret;
	}

	private function getEvents($account){
		$entries = Array ();
		$confId = 'pushd.events.';

		$events = $account->getConfigurations()->getKeyNames($confId);
		foreach($events As $event) {
			$label = $account->getConfigData($confId.$event.'.label');
			$entries[] = array('label' => $label ? $label : $event,
					'event'=>$event);
		}
		return $entries;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3socials/provider/class.tx_t3socials_provider_PushNotifications.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3socials/provider/class.tx_t3socials_provider_PushNotifications.php']);
}
<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Rene Nitzsche
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
tx_rnbase::load('tx_rnbase_util_SearchBase');


/**
 * Class to search networks from database
 * 
 * @author Rene Nitzsche
 */
class tx_t3socials_search_Network extends tx_rnbase_util_SearchBase {

	protected function getTableMappings() {
		$tableMapping['NETWORK'] = 'tx_t3socials_networks';

		// Hook to append other tables
		tx_rnbase_util_Misc::callHook('t3socials','search_Network_getTableMapping_hook',
			array('tableMapping' => &$tableMapping), $this);
		return $tableMapping;
	}

  protected function getBaseTable() {
  	return 'tx_t3socials_networks';
  }
  function getWrapperClass() {
  	return 'tx_t3socials_models_Network';
  }
	protected function getBaseTableAlias() {return 'NETWORK';}
  protected function useAlias() {
		return true;
	}

  protected function getJoins($tableAliases) {
  	$join = '';

  	// Hook to append other tables
		tx_rnbase_util_Misc::callHook('t3socials','search_Network_getJoins_hook',
			array('join' => &$join, 'tableAliases' => $tableAliases), $this);
  	return $join;
  }
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3socials/search/class.tx_t3socials_search_Network.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3socials/search/class.tx_t3socials_search_Network.php']);
}

?>
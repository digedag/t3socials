<?php
/***************************************************************
*  Copyright notice
*
 * (c) 2014 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

use DMK\T3rest\Legacy\Provider\AbstractProvider;
use Sys25\RnBase\Frontend\Request\Request;

/**
 * This is a REST provider for T3socials pushd network
 * UseCases:
 * get = teamUid -> return a specific team
 * getdefined = cfc1 -> return a specific preconfigured team.
 *
 * @author Rene Nitzsche <rene@system25.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 *
 * Former class tx_t3socials_provider_PushNotifications
 */
class PushNotifications extends AbstractProvider
{
    /**
     * @param tx_rnbase_configurations $configurations
     * @param string $confId
     *
     * @return tx_t3socials_model_Network
     */
    protected function handleRequest(Request $request)
    {
        $configurations = $request->getConfigurations();
        $confId = $request->getConfId();
        if ($tableAlias = $request->getParameters()->get('get')) {
            $data = $this->getNetwork($tableAlias, $configurations, $confId.'get.');
        }

        return $data;
    }

    /**
     * @return string
     */
    protected function getConfId()
    {
        return 't3socials.pushd.';
    }

    /**
     * @return string
     */
    protected function getBaseClass()
    {
        return 'tx_t3socials_models_Network';
    }

    /**
     * Lädt einen Account.
     *
     * @param string $tableAlias string-Identifier
     *
     * @return tx_t3socials_models_Network
     */
    private function getNetwork($tableAlias, $configurations, $confId)
    {
        $ret = false;
        // Prüfen, ob der Dienst konfiguriert ist
        $defined = $configurations->getKeyNames($confId.'defined.');
        if (in_array($tableAlias, $defined)) {
            $ret = new stdClass();
            $itemId = $configurations->get($confId.'defined.'.$tableAlias.'.network');
            $account = tx_rnbase::makeInstance('tx_t3socials_models_Network', $itemId);
            $ret = $this->getEvents($account);
        }

        return $ret;
    }

    /**
     * @param tx_t3socials_models_Network $account
     *
     * @return array
     */
    private function getEvents($account)
    {
        $entries = [];
        $confId = 'pushd.events.';

        $events = $account->getConfigurations()->getKeyNames($confId);
        foreach ($events as $event) {
            $label = $account->getConfigData($confId.$event.'.label');
            $hidden = $account->getConfigData($confId.$event.'.hidden');
            if (1 != intval($hidden)) {
                $entries[] = [
                    'label' => $label ? $label : $event,
                    'event' => $event,
                ];
            }
        }

        return $entries;
    }
}

<?php
/***************************************************************
*  Copyright notice
*
 * (c) 2013 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
tx_rnbase::load('tx_rnbase_util_Typo3Classes');

/**
 * TCE-HOOK.
 *
 * @author Rene Nitzsche <rene@system25.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class tx_t3socials_hooks_TCEHook
{
    /**
     * Nachbearbeitungen, unmittelbar NACHDEM die Daten gespeichert wurden.
     *
     * @param string $status
     * @param string $table
     * @param int $id
     * @param array $fieldArray
     * @param TYPO3\CMS\Core\DataHandling\DataHandler &$tcemain
     *
     * @return void
     */
    public function processDatamap_afterDatabaseOperations(
        $status,
        $table,
        $id,
        $fieldArray,
        &$tcemain
    ) {
        if (!(
                // gibts trigger für die Tabelle?
                $this->isTriggerable($table)
                // wurden daten geändert?
                && !empty($tcemain->datamap)
                // befinden wir uns im live workspace?
                && 0 === $tcemain->BE_USER->workspace
                // nur beim command new und update!
                && ('new' == $status || 'update' == $status)
            )
        ) {
            return;
        }
        if ('new' == $status) {
            $id = $tcemain->substNEWwithIDs[$id];
        }

        $this->handleAutoSend($table, $id);
        $this->handleInfo($table, $id);
    }

    /**
     * Hook after performing different record actions in Typo3 backend:
     * Update indexes according to the just performed action.
     *
     * @param string $command
     * @param string $table
     * @param int $id
     * @param int $value
     * @param TYPO3\CMS\Core\DataHandling\DataHandler $tce
     *
     * @return void
     *
     * @todo Treatment of any additional actions necessary?
     */
    public function processCmdmap_postProcess(
        $command,
        $table,
        $id,
        $value,
        $tcemain
    ) {
        if (!(
                // gibts trigger für die Tabelle?
                $this->isTriggerable($table)
                // wurden änderungen am workspace gemacht?
                && 'version' == $command
                // wurde die version ausgetauscht?
                && 'swap' === $value['action']
                && $value['swapWith'] > 0
            )
        ) {
            return;
        }

        $this->handleAutoSend($table, $id);
        $this->handleInfo($table, $id);
    }

    /**
     * Prüft, ob die übergebene Tabelle Trigger haben kann.
     *
     * @param string $table
     *
     * @return string
     */
    protected function isTriggerable($table)
    {
        $triggerable = tx_t3socials_trigger_Config::getTriggerTableNames();

        return in_array($table, $triggerable);
    }

    /**
     * Sendet automatisch einen Datensatz an die Netzwerke.
     *
     * @param string $table
     * @param int $uid
     *
     * @return void
     */
    protected function handleAutoSend($table, $uid)
    {
        $networkSrv = tx_t3socials_srv_ServiceRegistry::getNetworkService();
        $states = $networkSrv->exeuteAutoSend($table, $uid);
        tx_rnbase::load('tx_t3socials_util_Message');
        /* @var $state tx_t3socials_models_State */
        foreach ($states as $state) {
            // wir zeigen nur erfolgsmeldungen an,
            // alles weitere steht in der log
            if ($state->isStateSuccess()) {
                tx_t3socials_util_Message::showFlashMessage($state);
            }
        }
    }

    /**
     * Prüft, ob eine spezielle Info (Flash Message) erzeugt werden soll.
     *
     * @param string $table
     * @param int $uid
     *
     * @return void
     *
     * @todo seit TYPO3 7.6 wird auf die nachricht htmlspecialchars ausgeführt. Dadurch
     * werden die Links nicht korrekt angezeigt.
     */
    protected function handleInfo($table, $uid)
    {
        $triggers = tx_t3socials_trigger_Config::getTriggerNamesForTable($table);
        $networkSrv = tx_t3socials_srv_ServiceRegistry::getNetworkService();
        $networks = $networkSrv->findAccountsByTriggers($triggers, false);
        // wir haben Konfigurierte Netzwerke,
        // weche manuell getriggert werden können.
        // wir bauen also die nachricht zusammen
        if (!empty($networks)) {
            tx_rnbase::load('tx_rnbase_util_Misc');
            tx_rnbase::load('Tx_Rnbase_Backend_Utility');
            $url = Tx_Rnbase_Backend_Utility::getModuleUrl(
                'user_txt3socialsM1',
                [
                        'returnUrl' => rawurlencode(tx_rnbase_util_Misc::getIndpEnv('REQUEST_URI')),
                        'SET' => [
                            'function' => 'tx_t3socials_mod_Trigger',
                            'trigger' => reset($triggers),
                            'resource' => (int) $uid,
                        ],
                    ],
                ''
            );
            $msg = 'Sie können das eben gespeicherte Element über '.
                    'T3 SOCIALS an verschiedene Dienste senden. '.
                    'Wechseln Sie in das BE Modul von T3 SOCIALS oder rufen Sie die folgende URL auf, um die Nachricht '.
                    'anzupassen und einen manuellen Versand durchzuführen: '.$url
            ;
            $flashMessageClass = tx_rnbase_util_Typo3Classes::getFlashMessageClass();
            $message = [
                'message' => $msg,
                'title' => 'T3 SOCIALS',
                'severity' => $flashMessageClass::INFO,
                // Damit die meldung auch bei akttionen wie
                // speichern und schließen" ausgegeben wird.
                'storeinsession' => true,
            ];
            tx_rnbase::load('tx_t3socials_util_Message');
            tx_t3socials_util_Message::showFlashMessage($message);
        }
    }
}

if (defined('TYPO3_MODE') &&
    $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3socials/hooks/class.tx_t3socials_hooks_TCEHook.php']
) {
    include_once $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3socials/hooks/class.tx_t3socials_hooks_TCEHook.php'];
}

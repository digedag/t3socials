<?php
use Sys25\RnBase\Typo3Wrapper\Service\AbstractService;
use Sys25\RnBase\Database\Connection;
use Sys25\RnBase\Search\SearchBase;
use Sys25\RnBase\Utility\Logger;

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

/**
 * Service for accessing network account information.
 *
 * @author Rene Nitzsche <rene@system25.de>
 * @author Michael Wagner <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class tx_t3socials_srv_Network extends AbstractService
{
    const TABLE_AUTOSEND = 'tx_t3socials_autosends';

    /**
     * Return name of search class.
     *
     * @return string
     */
    public function getSearchClass()
    {
        return 'tx_t3socials_search_Network';
    }

    /**
     * Sendet automatisch einen Datensatz an die Netzwerke.
     *
     * @param string $table
     * @param int $uid
     *
     * @return array[tx_t3socials_models_State]
     *         Enthält eine statusmeldung für jedes netzwerk
     */
    public function exeuteAutoSend($table, $uid)
    {
        // alle trigger zur tabelle holen
        /* @var tx_t3socials_models_TriggerConfig $triggerConfig */
        $triggerConfig = tx_t3socials_trigger_Config::getTriggerConfigsForTable($table);

        // Doppelten Versand optional automatisch verhindern.
        if (!$triggerConfig->isDoubleSentAllowed() && $this->hasSent($uid, $table)) {
            return [];
        }

        // the resolver creates the record!
        $resolver = tx_t3socials_trigger_Config::getResolver($triggerConfig);
        $record = $resolver->getRecord($table, $uid);

        // wenn gelöscht oder versteckt, nicht publizieren!
        if (!$record instanceof tx_t3socials_models_Base
            || $record->isDeleted() || $record->isHidden()
        ) {
            return [];
        }

        // the builder generates the generic message
        $builder = tx_t3socials_trigger_Config::getMessageBuilder($triggerConfig);
        $message = $builder->buildGenericMessage($record);
        if (null === $message) {
            return [];
        }

        $accounts = $this->findAccountsByTriggers($triggerConfig->getTriggerId(), true);
        $states = $this->sendMessage($message, $accounts, $builder, $triggerConfig);

        foreach ($states as $state) {
            if (tx_t3socials_models_State::STATE_OK == $state->getState()) {
                $this->setSent($uid, $table);
                break;
            }
        }

        return $states;
    }

    /**
     * @param tx_t3socials_models_Message $message
     * @param array[tx_t3socials_models_Network] $accounts
     *
     * @return array[tx_t3socials_models_State]
     */
    public function sendMessage($message, $accounts, $builder, $triggerConfig)
    {
        $states = [];
        /* @var tx_t3socials_models_Network $network */
        foreach ($accounts as $network) {
            /* @var $state tx_t3socials_models_State */
            $state = tx_rnbase::makeInstance(
                'tx_t3socials_models_State',
                0
            );
            $state->setTriggerConfig($triggerConfig);
            $state->setMessageModel($message);
            $state->setNetworkModel($network);
            // spezielle netzwerk abhängige dinge durchführen.
            $builder->prepareMessageForNetwork(
                $message,
                $network,
                $triggerConfig
            );
            // verbindung aufbauen
            $connection = tx_t3socials_network_Config::getNetworkConnection($network);

            $msgId = '['.$network->getName().'->'.$triggerConfig->getTriggerId().'.'.$network->getNetwork().']';
            // nachricht senden
            try {
                $error = $connection->sendMessage($message);
                if ($error) {
                    Logger::warn(
                        'Error sending message! '.$msgId,
                        [
                            'error' => (string) $error,
                            'account' => (string) $network->getName(),
                            'message' => (string) $message,
                            'network' => (string) $network->getNetwork(),
                            'trigger' => (string) $triggerConfig->getTriggerId(),
                        ]
                    );
                    $state->setState(
                        tx_t3socials_models_State::STATE_WARNING,
                        'Error sending message! '.$msgId.
                        '. See DevLog for more Informations.'
                    );
                } // versand erfolgreich
                else {
                    $state->setState(
                        tx_t3socials_models_State::STATE_OK,
                        'Message successfully send to network! '.$msgId
                    );
                }
            } catch (Exception $e) {
                Logger::fatal(
                    'Error sending message! '.$msgId,
                    't3socials',
                    [
                        'exception' => (string) $e,
                        'account' => (string) $network->getName(),
                        'message' => (string) $message,
                        'network' => (string) $network->getNetwork(),
                        'trigger' => (string) $triggerConfig->getTriggerId(),
                    ]
                );
                $state->setState(
                    tx_t3socials_models_State::STATE_ERROR,
                    'Error sending message! '.$msgId.
                        '. See DevLog for more Informations.'
                );
            }
            $states[] = $state;
        }

        return $states;
    }

    /**
     * Liefert alle Netzwerke für einen trigger.
     * Eine Netzwerkkonfiguration kann mehrere Trigger haben!
     *
     * @param string|array $triggers
     *
     * @return array
     */
    public function findAccounts($triggers)
    {
        $triggers = is_array($triggers) ? implode(',', $triggers) : $triggers;
        $fields = $options = [];
        // @TODO: das funktioniert nur durch einen Bug in rn_base.
        // Bei OP_INSET_INT wird aktuell keine Typumwandlung zum Integer gemacht.
        // FIXME: Da muss ein weiterer Operator in rn_base angelegt werden. OP_INSET_STR
        $fields['NETWORK.actions'][OP_INSET_INT] = $triggers;

        return $this->search($fields, $options);
    }

    /**
     * Liefert alle Netzwerke für einen trigger, welche das Autosend-Flag haben.
     *
     * @param string|array $triggers
     * @param bool|null $autosend
     *
     * @return array
     */
    public function findAccountsByTriggers($triggers, $autosend = null)
    {
        $triggers = is_array($triggers) ? implode(',', $triggers) : $triggers;
        $fields = $options = [];
        // @TODO: das funktioniert nur durch einen Bug in rn_base.
        // Bei OP_INSET_INT wird aktuell keine Typumwandlung zum Integer gemacht.
        $fields['NETWORK.actions'][OP_INSET_INT] = $triggers;
        if (null !== $autosend) {
            $fields['NETWORK.autosend'][OP_EQ_INT] = $autosend ? 1 : 0;
        }

        return $this->search($fields, $options);
    }

    /**
     * Liefert alle Accounts eines Typs.
     *
     * @param string $types
     *
     * @return array
     */
    public function findAccountsByType($types)
    {
        $fields = $options = [];
        // FIXME: OP_INSET
        $fields['NETWORK.network'][OP_LIKE] = $types;

        return $this->search($fields, $options);
    }

    /**
     * Search database for networks.
     *
     * @param array $fields
     * @param array $options
     *
     * @return array of tx_t3socials_models_Network
     */
    public function search($fields, $options)
    {
        $searcher = SearchBase::getInstance('tx_t3socials_search_Network');

        return $searcher->search($fields, $options);
    }

    /**
     * Check if a record was send to networks before.
     *
     * @param int $uid
     * @param string $table
     *
     * @return bool
     */
    public function hasSent($uid, $table)
    {
        // enablefields gibts nicht für die tabelle
        $options['enablefieldsoff'] = 1;
        $table = Connection::getInstance()->fullQuoteStr($table, 'tx_t3socials_autosends');
        $options['where'] = 'recid = '.(int) $uid.' AND tablename = '.$table;
        $result = Connection::getInstance()->doSelect('count(uid) as cnt', self::TABLE_AUTOSEND, $options);

        return (int) $result[0]['cnt'] > 0;
    }

    /**
     * Markiert einen Datensatz als versendet.
     *
     * @param int $uid
     * @param string $table
     *
     * @return int  UID of created record
     */
    public function setSent($uid, $table)
    {
        if ($this->hasSent($uid, $table)) {
            return 0;
        }
        $values = [
            'recid' => $uid,
            'tablename' => $table,
        ];

        return Connection::getInstance()->doInsert(self::TABLE_AUTOSEND, $values);
    }
}


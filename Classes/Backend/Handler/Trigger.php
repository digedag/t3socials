<?php

namespace DMK\T3socials\Backend\Handler;

use DMK\T3socials\Utility\Template;
use Sys25\RnBase\Backend\Module\IModHandler;
use Sys25\RnBase\Backend\Module\IModule;
use Sys25\RnBase\Frontend\Marker\Templates;
use Sys25\RnBase\Frontend\Request\Parameters;
use Throwable;
use tx_rnbase;
use tx_t3socials_models_Base;
use tx_t3socials_models_Message;
use tx_t3socials_models_Network;
use tx_t3socials_models_TriggerConfig;
use tx_t3socials_network_Config;
use tx_t3socials_srv_ServiceRegistry;
use tx_t3socials_trigger_Config;

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
 * Trigger Handler.
 *
 * @author Michael Wagner <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class Trigger implements IModHandler
{
    private $triggerConfig = false;
    private $resourceModel = false;
    private $accountSelector = false;
    /**
     * @var Tx_Rnbase_Domain_Model_Base
     */
    private $formData;

    /**
     * Setzt den Trigger.
     *
     * @param tx_t3socials_models_TriggerConfig $triggerConfig
     *
     * @return self
     */
    public function setTriggerConfig(
        tx_t3socials_models_TriggerConfig $triggerConfig
    ) {
        $this->triggerConfig = $triggerConfig;

        return $this;
    }

    /**
     * Liefert den Trigger.
     *
     * @return tx_t3socials_models_TriggerConfig
     */
    public function getTriggerConfig()
    {
        return $this->triggerConfig;
    }

    /**
     * Setzt das Resource Model.
     *
     * @param tx_t3socials_models_Base $resourceModel
     *
     * @return self
     */
    public function setResourceModel(
        tx_t3socials_models_Base $resourceModel
    ) {
        $this->resourceModel = $resourceModel;

        return $this;
    }

    /**
     * Liefert das Resource Model.
     *
     * @return tx_t3socials_models_Base
     */
    public function getResourceModel()
    {
        return $this->resourceModel;
    }

    /**
     * Liefert die eventuell abgesendeten Formulardaten.
     *
     * @return tx_t3socials_models_Base
     */
    protected function getFormData()
    {
        if (null === $this->formData) {
            $data = Parameters::getPostOrGetParameter('data');
            $data = empty($data) || !is_array($data) ? [] : $data;
            // keine formdaten vorhanden? dann die vom record nutzen!
            if (empty($data) && $this->getTriggerConfig() && $this->getResourceModel()) {
                $builder = tx_t3socials_trigger_Config::getMessageBuilder($this->getTriggerConfig());
                $this->formData = $builder->buildGenericMessage($this->getResourceModel());
            } else {
                $this->formData = tx_rnbase::makeInstance(tx_t3socials_models_Base::class, $data);
            }
        }

        return $this->formData;
    }

    /**
     * Liefert die generische Nachricht für den versand.
     *
     * @return tx_t3socials_models_Message
     */
    protected function getMessage()
    {
        $formData = $this->getFormData();
        $type = $this->getTriggerConfig() ? $this->getTriggerConfig()->getTriggerId() : 'manually';
        $message = tx_t3socials_models_Message::getInstance($type);
        $message->setHeadline($formData->getHeadline());
        $message->setIntro($formData->getIntro());
        $message->setMessage($formData->getMessage());
        $message->setUrl($formData->getUrl());

        return $message;
    }

    /**
     * Liefert alle im Default Formular sichtbaren Felder.
     *
     * @return array
     */
    protected function getVisibleFormFields()
    {
        return ['headline', 'intro', 'message', 'url'];
    }

    /**
     * Returns a unique ID for this handler.
     * This is used to created the subpart in template.
     *
     * @return string
     */
    public function getSubID()
    {
        return 'trigger';
    }

    /**
     * Liefert das Label des Moduls.
     *
     * @return string
     */
    public function getSubLabel()
    {
        return 'Trigger';
    }

    /**
     * This method is called each time the method func is clicked,
     * to handle request data.
     *
     * @param IModule $mod
     *
     * @return string|null
     */
    public function handleRequest(IModule $mod)
    {
        $submitted = Parameters::getPostOrGetParameter('sendtriggermessage');
        if (!$submitted) {
            return null;
        }

        $networks = Parameters::getPostOrGetParameter('network');

        // keine netzwerke / accounts gewählt
        if (empty($networks)) {
            $mod->addMessage('###LABEL_MESSAGE_NO_NETWORK_SELECTED###', '###LABEL_MESSAGE###', 0);

            return null;
        }

        // uids sind immer int!
        $networks = array_map('intval', $networks);

        $message = $this->getMessage();

        if (!$message->getHeadline()
            && !$message->getIntro()
            && !$message->getMessage()
            && !$message->getUid()
        ) {
            $message = '###LABEL_MESSAGE_EMPTY###';
        }

        // Wurde keine Message zurück gegeben
        // ist die Validierung fehlgeschlagen!
        if (!$message instanceof tx_t3socials_models_Message) {
            $mod->addMessage($message, '###LABEL_MESSAGE###', 1);

            return null;
        }

        // wir initiieren noch das model in die news
        $message->setData($this->getResourceModel());

        $hasSend = false;

        $networkSrv = tx_t3socials_srv_ServiceRegistry::getNetworkService();
        foreach ($networks as $networkId) {
            /* @var $account tx_t3socials_models_Network */
            $account = $networkSrv->get($networkId);

            // wir erzeugen ein clone, um spezielle manipulationen
            // für das netzwerk zu machen
            $messageCopy = clone $message;

            // jetzt müssen wir nocht den builder vom trigger aufrufen
            // das ist wichtig, wenn beispielsweise der link
            // zu einer news automatich generiert werden soll
            if ($this->getTriggerConfig()) {
                $builder = tx_t3socials_trigger_Config::getMessageBuilder($this->getTriggerConfig());
                $builder->prepareMessageForNetwork(
                    $messageCopy,
                    $account,
                    $this->getTriggerConfig()
                );
            }

            try {
                $connection = tx_t3socials_network_Config::getNetworkConnection($account);
                $connection->setNetwork($account);
                $error = $connection->sendMessage($messageCopy);
                // fehler beim senden?
                if ($error) {
                    $mod->addMessage($error, '###LABEL_ERROR### ('.$account->getName().')', 1);
                } // erfolgreich versendet
                else {
                    $mod->addMessage('###LABEL_MESSAGE_SENT###', '###LABEL_NOTE### ('.$account->getName().')', 0);
                    $hasSend = true;
                }
            } catch (Throwable $e) {
                $mod->addMessage($e->getMessage(), '###LABEL_ERROR### ('.$account->getName().')', 2);
            }
        }

        if ($hasSend && $this->getTriggerConfig() && $this->getResourceModel()) {
            $networkSrv->setSent(
                $this->getResourceModel()->getUid(),
                $this->getTriggerConfig()->getTableName()
            );
        }

        return null;
    }

    /**
     * Display the user interface for this handler.
     *
     * @param string $template
     * @param IModule $mod
     * @param array $options
     *
     * @return string
     */
    public function showScreen($template, IModule $mod, $options)
    {
        $options['submitname'] = empty($options['submitname']) ? 'sendtriggermessage' : $options['submitname'];
        $out = Template::parseMessageFormFields(
            $template,
            $mod,
            $this->getFormData(),
            array_flip($this->getVisibleFormFields()),
            $options
        );

        $markerArr = [];
        // felder vom basistemplate befüllen
        $markerArr['###NETWORK_TITLE###'] = 'Accounts and Message';
        $markerArr['###AUTH_STATE###'] = $this->getResourceModelInfo($mod);
        $markerArr['###ACCOUNT_SEL###'] = $this->getAccountSelector($mod);
        $markerArr['###ACCOUNT_EDITLINK###'] = '';
        $out = Templates::substituteMarkerArrayCached($out, $markerArr);

        return $out;
    }

    /**
     * Liefert eine Kurze Info über das verwendete Resource-Model.
     *
     * @param IModule $mod
     *
     * @return string
     */
    protected function getResourceModelInfo(IModule $mod)
    {
        $out = '';
        $model = $this->getResourceModel();
        $trigger = $this->getTriggerConfig();
        if ($model) {
            $tableName = $trigger->getTableName();
            $labelField = $GLOBALS['TCA'][$tableName]['ctrl']['label'];
            $row = [];
            $row[] = ['###LABEL_RESOURCE_INFO###', ''];
            $row[] = [
                '###LABEL_T3SOCIALS_TRIGGER###',
                $trigger->getTriggerId(),
            ];
            $networkSrv = tx_t3socials_srv_ServiceRegistry::getNetworkService();
            $hasSend = $networkSrv->hasSent($model->getUid(), $tableName);
            $wrap = [
                '<span style="color:#'.($hasSend ? 'AA0225' : '3B7826').';">',
                '</span>',
            ];
            $row[] = [
                $wrap[0].'###LABEL_T3SOCIALS_HASSEND_LABEL###'.$wrap[1],
                $wrap[0].($hasSend ? '###LABEL_T3SOCIALS_HASSEND_YES###' : '###LABEL_T3SOCIALS_HASSEND_NO###').$wrap[1],
            ];
            $row[] = [
                '###LABEL_TABLE###',
                $tableName,
            ];
            $row[] = [
                'UID',
                $model->getUid(),
            ];
            $row[] = [
                '###LABEL_TITLE###',
                $model->getProperty[$labelField],
            ];
            // gelöscht oder hidden? dann meldung ausgeben!
            $state = [];
            if ($model->isDeleted()) {
                $state[] = 'deleted';
            }
            if ($model->isHidden()) {
                $state[] = 'hidden';
            }
            if (!empty($state)) {
                $wrap = [
                    '<span style="color:#AA0225;">',
                    '</span>',
                ];
                $row[] = [
                    $wrap[0].'State'.$wrap[1],
                    $wrap[0].implode(' and ', $state).'!'.$wrap[1],
                ];
            }

            $out = tx_rnbase::makeInstance('Tx_Rnbase_Backend_Utility_Tables')->buildTable($row);
        }

        return $out;
    }

    /**
     * Liefert alle Accounts für die Auswahl zum versenden.
     *
     * @param IModule $mod
     *
     * @return array
     */
    private function getAccountSelector(IModule $mod)
    {
        if (false === $this->accountSelector) {
            $this->accountSelector = '';

            $srv = tx_t3socials_srv_ServiceRegistry::getNetworkService();

            $accounts = [];
            $triggerConfig = $this->getTriggerConfig();
            // nur accounts für einen bestimmten Trigger liefern
            if ($triggerConfig) {
                $accounts = $srv->findAccounts($triggerConfig->getTriggerId());
            } // alle accounts abrufen
            else {
                $accounts = $srv->findAll();
            }

            $rows = [];
            // tabellenüberschrift
            $rows[] = ['', '###LABEL_ACCOUNT###', '###LABEL_STATE###'];

            foreach ($accounts as $account) {
                $rows[] = $this->getAccountRow($account, $mod);
            }

            $tables = tx_rnbase::makeInstance('Tx_Rnbase_Backend_Utility_Tables');
            $this->accountSelector = $tables->buildTable($rows);
        }

        return $this->accountSelector;
    }

    /**
     * Erzeugt das HTML für den Select eines Netzwerks.
     *
     * @param tx_t3socials_models_Network $account
     * @param IModule $mod
     *
     * @return array
     */
    protected function getAccountRow(
        tx_t3socials_models_Network $account,
        IModule $mod
    ) {
        $checked = Parameters::getPostOrGetParameter('network');
        $checked = is_array($checked) ? $checked : [];

        $uid = $account->getUid();
        $title = $account->getName();

        $row = [];

        $html = '';
        $html .= '<input type="checkbox"';
        $html .= ' name="network['.$uid.']"';
        $html .= ' id="network_'.$uid.'"';
        $html .= ' value="'.$uid.'"';
        $html .= empty($checked[$uid]) ? '' : ' checked="checked"';
        $html .= ' /> ';
        $row[] = $html;

        $html = '';
        $html .= $mod->getFormTool()->createEditLink($account->getTableName(), $uid, '');
        $html .= ' <label for="network_'.$uid.'">';
        $html .= ' <strong>'.$title.'</strong>';
        $html .= ' </label>';
        $row[] = $html;

        $row[] = Template::getAuthentificationState($account);

        return $row;
    }
}

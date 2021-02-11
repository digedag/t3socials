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
tx_rnbase::load('tx_rnbase_parameters');
tx_rnbase::load('tx_rnbase_mod_BaseModFunc');
tx_rnbase::load('Tx_Rnbase_Backend_Utility');

/**
 * Backend Modul für Nachrichtenversand.
 *
 * @author Michael Wagner <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class tx_t3socials_mod_Trigger extends tx_rnbase_mod_BaseModFunc
{
    private $triggerSelector = false;
    private $triggerConfig = false;
    private $resourceSelector = false;
    private $resourceModel = false;

    /**
     * Method getFuncId.
     *
     * @return  string
     */
    protected function getFuncId()
    {
        return 'trigger';
    }

    /**
     * Kindklassen implementieren diese Methode um den Modulinhalt zu erzeugen.
     *
     * @param string $template
     * @param tx_rnbase_configurations &$configurations
     * @param tx_rnbase_util_FormatUtil &$formatter
     * @param tx_rnbase_util_FormTool $formTool
     *
     * @return string
     */
    protected function getContent($template, &$configurations, &$formatter, $formTool)
    {
        if (tx_rnbase_parameters::getPostOrGetParameter('trigger_back_resourceselector')) {
            // @TODO: das funktioniert noch nicht wie es soll.
            // daten bleiben teilweise erhalten
            Tx_Rnbase_Backend_Utility::getModuleData(
                ['resource' => ''],
                ['resource' => ''],
                $this->getModule()->getName()
            );
            $this->resourceModel = null;
        }

        $markerArray = [];

        $subOut = '';
        if ($this->getTrigger() && $this->getResource()) {
            $subOut = tx_rnbase_util_Templates::getSubpart($template, '###NETWORKS###');
            $subOut = $this->showNetworks($subOut, $configurations, $formatter, $markerArray);
        } else {
            $subOut = tx_rnbase_util_Templates::getSubpart($template, '###TRIGGERRESOURCE###');
            $subOut = $this->showResourceSelector($subOut, $configurations, $formatter, $markerArray);
        }

        // ggf returnUrl auswerten
        $returnUrl = tx_rnbase_parameters::getPostOrGetParameter('returnUrl');
        if ($returnUrl) {
            // returnUrl weiter geben!
            $content .= '<p style="position:absolute; top:-5000px; left:-5000px;">'.
                    '<input type="hidden" value="'.$returnUrl.'" />'.
                '</p>';
            // zurück button an return url generieren.
            $markerArray['###BTN_BACK###'] = '<input type="submit"'.
                ' value="###LABEL_BTN_BACK###"'.
                ' name="trigger_back_resourceselector"'.
                ' onclick="window.location.href=\''.rawurldecode($returnUrl).'\'; return false;"'.
                ' />';
        }

        $markerArray['###BTN_REFRESH###'] = $this->getModule()->getFormTool()->createSubmit(
            'refresh',
            '###LABEL_BTN_REFRESH###'
        );

        $subOut = tx_rnbase_util_Templates::substituteMarkerArrayCached($subOut, $markerArray);

        $content = '';
        $content .= tx_rnbase_util_Templates::getSubpart($template, '###COMMON_START###');
        $content .= $subOut;
        $content .= tx_rnbase_util_Templates::getSubpart($template, '###COMMON_END###');

        return $content;
    }

    /**
     * Kindklassen implementieren diese Methode um den Modulinhalt zu erzeugen.
     *
     * @param string $template
     * @param tx_rnbase_configurations &$configurations
     * @param tx_rnbase_util_FormatUtil &$formatter
     * @param array &$markerArray
     *
     * @return string
     */
    protected function showResourceSelector($template, &$configurations, &$formatter, &$markerArray)
    {
        $markerArray['###TRIGGERSELECTOR###'] = $this->getTriggerMenue();
        $markerArray['###RESOURCESELECTOR###'] = $this->getResourceMenue();

        return $template;
    }

    /**
     * Kindklassen implementieren diese Methode um den Modulinhalt zu erzeugen.
     *
     * @param string $template
     * @param tx_rnbase_configurations &$configurations
     * @param tx_rnbase_util_FormatUtil &$formatter
     * @param array &$markerArray
     *
     * @return string
     */
    protected function showNetworks($template, &$configurations, &$formatter, &$markerArray)
    {
        $module = $this->getModule();

        $options = [];
        /* @var $handler tx_t3socials_mod_handler_Trigger */
        $handler = tx_rnbase::makeInstance('tx_t3socials_mod_handler_Trigger');
        $handler->setTriggerConfig($this->getTrigger());
        $handler->setResourceModel($this->getResource());

        $message = $handler->handleRequest($module);
        if ($message) {
            tx_t3socials_mod_util_Message::showMessage($message);
        }

        // wir übergeben mit absicht ein leeres template, um das default zu nutzen
        $out = $handler->showScreen('', $module, $options);

        $markerArray['###MESSAGE_FORM###'] = $out;
        $markerArray['###BTN_BACK###'] = $module->getFormTool()->createSubmit(
            'trigger_back_resourceselector',
            '###LABEL_BTN_BACK###'
        );

        return $template;
    }

    /**
     * Liefert den aktuell gewählten Trigger.
     *
     * @return tx_t3socials_models_TriggerConfig
     */
    protected function getTrigger()
    {
        if (false === $this->triggerConfig) {
            $this->triggerConfig = null;
            $triggerMenu = $this->getTriggerSelector();
            $triggerId = $triggerMenu['value'];
            if (!empty($triggerId)) {
                $this->triggerConfig = tx_t3socials_trigger_Config::getTriggerConfig($triggerId);
            }
        }

        return $this->triggerConfig;
    }

    /**
     * Liefert die den aktuell gewählten Datensatze.
     *
     * @return tx_t3socials_models_Base
     */
    protected function getResource()
    {
        if (false === $this->resourceModel) {
            $this->resourceModel = null;
            $trigger = $this->getTrigger();
            if ($trigger) {
                $menu = $this->getResourceSelector();
                $id = $menu['value'];
                if (!empty($id)) {
                    $resolver = tx_t3socials_trigger_Config::getResolver($trigger);
                    $this->resourceModel = $resolver->getRecord($trigger->getTableName(), $id);
                }
            }
        }

        return $this->resourceModel;
    }

    /**
     * Returns the trigger selector.
     *
     * @return array
     */
    private function getTriggerSelector()
    {
        if (false === $this->triggerSelector) {
            $module = $this->getModule();
            $entries = ['' => ''];
            $trigger = tx_t3socials_trigger_Config::getTriggerIds();
            sort($trigger);
            foreach ($trigger as $k) {
                $entries[$k] = tx_t3socials_trigger_Config::translateTrigger($k);
            }
            $this->triggerSelector = $module->getFormTool()->showMenu(
                $module->getPid(),
                'trigger',
                $module->getName(),
                $entries
            );
        }

        return $this->triggerSelector;
    }

    /**
     * Liefert das resource menü
     *
     * @return string
     */
    private function getTriggerMenue()
    {
        $menue = $this->getTriggerSelector();

        return $menue['menu'];
    }

    /**
     * Returns resource selector.
     *
     * @TODO: das muss anders gestaltet werden!
     * Die Liste kann sehr, sehr lang und unübersichtlich werden.
     * Ein Autocomplete oder einschränkung für noch nicht versendete
     * Nachrichten wären sinnvoll.
     *
     * @return array
     */
    private function getResourceSelector()
    {
        if (false === $this->resourceSelector) {
            $this->resourceSelector = null;
            $trigger = $this->getTrigger();

            if ($trigger) {
                $rows = [];
                $module = $this->getModule();
                $tableName = $trigger->getTableName();
                if ($tableName) {
                    // Not every trigger config is based on database tables!
                    $labelField = $GLOBALS['TCA'][$tableName]['ctrl']['label'];

                    $options = [];
                    $rows = Tx_Rnbase_Database_Connection::getInstance()->doSelect(
                        'uid,'.$labelField,
                        $trigger->getTableName(),
                        $options
                    );
                }

                $entries = ['', ''];
                foreach ($rows as $record) {
                    $entries[$record['uid']] = $record[$labelField];
                }

                $this->resourceSelector = $module->getFormTool()->showMenu(
                    $module->getPid(),
                    'resource',
                    $module->getName(),
                    $entries
                );
            }
        }

        return $this->resourceSelector;
    }

    /**
     * Liefert das resource menü
     *
     * @return string
     */
    private function getResourceMenue()
    {
        $menue = $this->getResourceSelector();

        return empty($menue['menu']) ? '###LABEL_NO_TRIGGER_SELECTED###' : $menue['menu'];
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3socials/mod/class.tx_t3socials_mod_Trigger.php']) {
    include_once $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3socials/mod/class.tx_t3socials_mod_Trigger.php'];
}

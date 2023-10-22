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
tx_rnbase::load('tx_rnbase_util_Typo3Classes');
tx_rnbase::load('tx_t3socials_models_Base');

/**
 * Das Netzwerk Model.
 *
 * @author Rene Nitzsche <rene@system25.de>
 * @author Michael Wagner <dev@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class tx_t3socials_models_Network extends tx_t3socials_models_Base
{
    /**
     * @var tx_rnbase_configurations
     */
    private $configurations;

    /**
     * Inits the model instance either with uid or a complete data record.
     * As the result the instance should be completly loaded.
     *
     * @param mixed $rowOrUid
     *
     * @return void
     */
    public function init($rowOrUid = null)
    {
        parent::init($rowOrUid);
        $this->initConfig();
    }

    /**
     * Extract data from config.
     *
     * @return void
     */
    protected function initConfig()
    {
        $ts = $this->getProperty('config');
        // This handles ts setup from flexform
        /* @var $tsParser TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser */
        $tsParser = tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getTypoScriptParserClass());
        // $tsParser->setup = $this->_dataStore->getArrayCopy();
        $tsParser->parse($ts);
        $configArr = $tsParser->setup;
        tx_rnbase::load('tx_rnbase_configurations');
        $this->configurations = new tx_rnbase_configurations();
        $this->configurations->init($configArr, false, '', '');
    }

    /**
     * Returns the network identifier.
     *
     * @return string
     */
    public function getNetwork()
    {
        return $this->getProperty('network');
    }

    /**
     * Liefert den Name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getProperty('name');
    }

    /**
     * Liefert den username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->getProperty('username');
    }

    /**
     * Liefert das password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->getProperty('password');
    }

    /**
     * Returns configured data.
     *
     * @param string $confId
     *
     * @return string|array
     */
    public function getConfigData($confId)
    {
        return $this->configurations->get($confId);
    }

    /**
     * Returns the configuration for this account.
     *
     * @return tx_rnbase_configurations
     */
    public function getConfigurations()
    {
        return $this->configurations;
    }

    /**
     * Liefert den Namen der gemappten Tabelle.
     *
     * @return string
     */
    public function getTableName()
    {
        return 'tx_t3socials_networks';
    }
}

if (defined('TYPO3_MODE')
    && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3socials/models/class.tx_t3socials_models_Network.php']
) {
    include_once $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/t3socials/models/class.tx_t3socials_models_Network.php'];
}

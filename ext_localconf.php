<?php

use Sys25\RnBase\Utility\TYPO3;

if (!(defined('TYPO3') || defined('TYPO3_MODE'))) {
    exit('Access denied.');
}

/* *** ***************** *** *
 * *** Register Networks *** *
 * *** ***************** *** */
tx_t3socials_network_Config::registerNetwork(
    'tx_t3socials_network_pushd_NetworkConfig'
);
tx_t3socials_network_Config::registerNetwork(
    'tx_t3socials_network_twitter_NetworkConfig'
);
tx_t3socials_network_Config::registerNetwork(
    'tx_t3socials_network_xing_NetworkConfig'
);
tx_t3socials_network_Config::registerNetwork(
    'tx_t3socials_network_facebook_NetworkConfig'
);

/* *** **************** *** *
 * *** Register Trigger *** *
 * *** **************** *** */
if (Sys25\RnBase\Utility\Extensions::isLoaded('tt_news')) {
    tx_t3socials_trigger_Config::registerTrigger(
        DMK\T3socials\Trigger\TtNews\TriggerConfig::class
    );
}
if (Sys25\RnBase\Utility\Extensions::isLoaded('news')) {
    tx_t3socials_trigger_Config::registerTrigger(
        DMK\T3socials\Trigger\News\TriggerConfig::class
    );
}

/* *** ****************** *** *
 * *** HybridAuth (FE/BE) *** *
 * *** ****************** *** */
// ajax id for BE
if (!TYPO3::isTYPO121OrHigher()) {
    // FIXME: wird das noch benötigt? 
    Sys25\RnBase\Utility\Extensions::registerAjaxHandler(
        't3socials-hybridauth',
        Sys25\RnBase\Utility\Extensions::extPath(
            't3socials',
            'network/hybridauth/class.tx_t3socials_network_hybridauth_OAuthCall.php'
        ).
        ':tx_t3socials_network_hybridauth_OAuthCall->ajaxId',
        false
    );
}

/* *** ***** *** *
 * *** Hooks *** *
 * *** ***** *** */
// TCE-Hooks, um automatisch beim speichern trigger aufzurufen
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['t3socials']
    = DMK\T3socials\Hook\TCEHook::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['t3socials']
    = DMK\T3socials\Hook\TCEHook::class;

/* *** ***************** *** *
 * *** Register Services *** *
 * *** ***************** *** */
// FIXME: Umstellung auf Symfony
if (!TYPO3::isTYPO121OrHigher()) {
    Sys25\RnBase\Utility\Extensions::addService(
        $_EXTKEY,
        't3socials' /* sv type */ ,
        'tx_t3socials_srv_Network' /* sv key */ ,
        [
            'title' => 'Social network accounts', 'description' => 'Handles accounts of social networks', 'subtype' => 'network',
            'available' => true, 'priority' => 50, 'quality' => 50,
            'os' => '', 'exec' => '',
            'className' => 'tx_t3socials_srv_Network',
        ]
    );
};

/* *** ****************** *** *
 * *** System Enviroments *** *
 * *** ****************** *** */
defined('TAB') || define('TAB', chr(9));
defined('LF') || define('LF', chr(10));
defined('CR') || define('CR', chr(13));
defined('CRLF') || define('CRLF', CR.LF);

// eigenes input Feld wegen Vorbelegung vom config Feld
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry']['t3socials_networkConfigField'] = [
    'nodeName' => 'networkConfigField',
    'priority' => '70',
    'class' => \DMK\T3socials\Backend\Form\Element\NetworkConfigField::class,
];

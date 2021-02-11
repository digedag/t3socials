<?php

if (!defined('TYPO3_MODE')) {
    exit('Access denied.');
}

/* *** ***************** *** *
 * *** Register Networks *** *
 * *** ***************** *** */
tx_rnbase::load('tx_t3socials_network_Config');
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
tx_rnbase::load('tx_t3socials_trigger_Config');
if (tx_rnbase_util_Extensions::isLoaded('tt_news')) {
    tx_t3socials_trigger_Config::registerTrigger(
        'tx_t3socials_trigger_news_TriggerConfig'
    );
}

/* *** ****************** *** *
 * *** HybridAuth (FE/BE) *** *
 * *** ****************** *** */
// ajax id for BE
tx_rnbase_util_Extensions::registerAjaxHandler(
    't3socials-hybridauth',
    tx_rnbase_util_Extensions::extPath(
        't3socials',
        'network/hybridauth/class.tx_t3socials_network_hybridauth_OAuthCall.php'
    ).
    ':tx_t3socials_network_hybridauth_OAuthCall->ajaxId',
    false
);

/* *** ***** *** *
 * *** Hooks *** *
 * *** ***** *** */
// TCE-Hooks, um automatisch beim speichern trigger aufzurufen
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['t3socials']
    = 'EXT:t3socials/hooks/class.tx_t3socials_hooks_TCEHook.php:tx_t3socials_hooks_TCEHook';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['t3socials']
    = 'EXT:t3socials/hooks/class.tx_t3socials_hooks_TCEHook.php:tx_t3socials_hooks_TCEHook';

/* *** ***************** *** *
 * *** Register Services *** *
 * *** ***************** *** */
tx_rnbase::load('tx_t3socials_srv_ServiceRegistry');
tx_rnbase_util_Extensions::addService(
    $_EXTKEY,
    't3socials' /* sv type */ ,
    'tx_t3socials_srv_Network' /* sv key */ ,
    [
        'title' => 'Social network accounts', 'description' => 'Handles accounts of social networks', 'subtype' => 'network',
        'available' => true, 'priority' => 50, 'quality' => 50,
        'os' => '', 'exec' => '',
        'classFile' => tx_rnbase_util_Extensions::extPath($_EXTKEY, 'srv/class.tx_t3socials_srv_Network.php'),
        'className' => 'tx_t3socials_srv_Network',
    ]
);

/* *** ****************** *** *
 * *** System Enviromends *** *
 * *** ****************** *** */
defined('TAB') || define('TAB', chr(9));
defined('LF') || define('LF', chr(10));
defined('CR') || define('CR', chr(13));
defined('CRLF') || define('CRLF', CR.LF);

if (tx_rnbase_util_TYPO3::isTYPO76OrHigher()) {
    // eigenes input Feld wegen Vorbelegung vom config Feld
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry']['t3socials_networkConfigField'] = [
        'nodeName' => 'networkConfigField',
        'priority' => '70',
        'class' => \DMK\T3socials\Backend\Form\Element\NetworkConfigField::class,
    ];
}

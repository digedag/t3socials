<?php

if (!defined('TYPO3_MODE')) {
    exit('Access denied.');
}

/* *** **************** *** *
 * *** BE Module Config *** *
 * *** **************** *** */
if (TYPO3_MODE == 'BE') {
    // Einbindung einer PageTSConfig
    tx_rnbase_util_Extensions::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:'.'t3socials'.'/mod/pageTSconfig.txt">'
    );

    tx_rnbase_util_Extensions::registerModule(
        't3socials',
        'web',
        'M1',
        'bottom',
        [],
        [
            'access' => 'user,group',
            'routeTarget' => 'tx_t3socials_mod_Module',
            'icon' => 'EXT:t3socials/mod/moduleicon.png',
            'labels' => 'LLL:EXT:t3socials/mod/locallang.xml',
        ]
    );

    // communicator
    tx_rnbase_util_Extensions::insertModuleFunction(
        'web_T3socialsM1',
        'tx_t3socials_mod_Communicator',
        tx_rnbase_util_Extensions::extPath('t3socials', 'mod/class.tx_t3socials_mod_Communicator.php'),
        'LLL:EXT:t3socials/mod/locallang.xml:label_t3socials_connector'
    );
    // trigger
    tx_rnbase_util_Extensions::insertModuleFunction(
        'web_T3socialsM1',
        'tx_t3socials_mod_Trigger',
        tx_rnbase_util_Extensions::extPath('t3socials', 'mod/class.tx_t3socials_mod_Trigger.php'),
        'LLL:EXT:t3socials/mod/locallang.xml:label_t3socials_trigger'
    );
}

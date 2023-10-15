<?php

if (!defined('TYPO3_MODE')) {
    exit('Access denied.');
}

$configFieldWizards = tx_rnbase_util_TYPO3::isTYPO76OrHigher() ? [] : [
    'appendDefaultTSConfig' => [
        'type' => 'userFunc',
        'notNewRecords' => 1,
        'userFunc' => 'EXT:t3socials/util/class.tx_t3socials_util_TCA.php:tx_t3socials_util_TCA->insertNetworkDefaultConfig',
        'params' => [
            'insertBetween' => ['>', '</textarea'],
            'onMatchOnly' => '/^\s*$/',
        ],
    ],
];

$t3socials_Network = [
    'ctrl' => [
        'title' => 'LLL:EXT:t3socials/Resources/Private/Language/locallang_db.xml:tx_t3socials_networks',
        'label' => 'name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => true,
        'default_sortby' => 'ORDER BY name asc',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'requestUpdate' => 'network',
        'iconfile' => 'EXT:t3socials/ext_icon.gif',
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden,name,username,autosend',
    ],
    'feInterface' => [
        'fe_admin_fieldList' => 'name,username,password,config',
    ],
    'columns' => [
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config' => [
                'type' => 'check',
                'default' => '0',
            ],
        ],
        'network' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:t3socials/Resources/Private/Language/locallang_db.xml:tx_t3socials_networks_network',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [['', '']],
                'itemsProcFunc' => DMK\T3socials\Utility\TcaLookup::class.'->getNetworks',
                'size' => '1',
                'maxitems' => '1',
            ],
            'onChange' => 'reload',
        ],
        'name' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:t3socials/Resources/Private/Language/locallang_db.xml:tx_t3socials_networks_name',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'trim,required',
            ],
        ],
        'username' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:t3socials/Resources/Private/Language/locallang_db.xml:tx_t3socials_networks_username',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'trim',
            ],
        ],
        'password' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:t3socials/Resources/Private/Language/locallang_db.xml:tx_t3socials_networks_password',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'trim',
            ],
        ],
        'actions' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:t3socials/Resources/Private/Language/locallang_db.xml:tx_t3socials_networks_actions',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'itemsProcFunc' => DMK\T3socials\Utility\TcaLookup::class.'->getTriggers',
                'size' => '5',
                'maxitems' => '999',
            ],
        ],
        'autosend' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:t3socials/Resources/Private/Language/locallang_db.xml:tx_t3socials_networks_autosend',
            'config' => [
                'type' => 'check',
                'default' => '0',
            ],
        ],
        'config' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:t3socials/Resources/Private/Language/locallang_db.xml:tx_t3socials_networks_config',
            // Show only, if an Network was Set!
            'displayCond' => 'FIELD:network:REQ:TRUE',
            'config' => [
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
                'eval' => 'trim',
                'wizards' => $configFieldWizards,
                // @see DMK\T3socials\Backend\Form\Element\NetworkConfigField
                'renderType' => 'networkConfigField',
            ],
        ],
        'description' => [
            'exclude' => 0,
            'label' => '',
            // Show only, if an Network was Set!
            'displayCond' => 'FIELD:network:REQ:TRUE',
            'config' => [
                'type' => 'user',
                'userFunc' => DMK\T3socials\Utility\TcaLookup::class.'->insertNetworkDescription',
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'hidden,--palette--;;network_palette,name,username,password,actions,autosend,config'],
    ],
    'palettes' => [
        'network_palette' => ['showitem' => '--linebreak--,network,description'],
    ],
];

return $t3socials_Network;

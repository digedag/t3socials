<?php

if (!(defined('TYPO3') || defined('TYPO3_MODE'))) {
    exit('Access denied.');
}

/* *** **************** *** *
 * *** BE Module Config *** *
 * *** **************** *** */
if (\Sys25\RnBase\Utility\Environment::isBackend()) {
    if (!\Sys25\RnBase\Utility\TYPO3::isTYPO121OrHigher()) {
        $modName = 'web_T3socialsM1';

        // Einbindung einer PageTSConfig
        if (!\Sys25\RnBase\Utility\TYPO3::isTYPO121OrHigher()) {
            \Sys25\RnBase\Utility\Extensions::addPageTSConfig(
                '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:t3socials/Configuration/page.tsconfig">');
        }

        \Sys25\RnBase\Utility\Extensions::registerModule(
            't3socials',
            'web',
            'M1',
            'bottom',
            [],
            [
                'access' => 'user,group',
                'routeTarget' => DMK\T3socials\Backend\Module\T3socialsModule::class,
                'icon' => 'EXT:t3socials/Resource/Public/Icons/moduleicon.png',
                'labels' => 'LLL:EXT:t3socials/Resources/Private/Language/locallang_mod.xlf',
            ]
        );

        // communicator
        \Sys25\RnBase\Utility\Extensions::insertModuleFunction(
            $modName,
            DMK\T3socials\Backend\Controller\Communicator::class,
            '',
            'LLL:EXT:t3socials/Resources/Private/Language/locallang_mod.xlf:label_t3socials_connector'
        );
        // trigger
        \Sys25\RnBase\Utility\Extensions::insertModuleFunction(
            $modName,
            DMK\T3socials\Backend\Controller\Trigger::class,
            '',
            'LLL:EXT:t3socials/Resources/Private/Language/locallang_mod.xlf:label_t3socials_trigger'
        );
    }

    $iconRegistry = tx_rnbase::makeInstance(TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $icons = [
        'ext-t3socials-ext-default' => 'moduleicon.png',
    ];
    foreach ($icons as $identifier => $path) {
        $iconRegistry->registerIcon($identifier, TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class, [
            'source' => 'EXT:t3socials/Resources/Public/Icons/'.$path,
        ]);
    }
}

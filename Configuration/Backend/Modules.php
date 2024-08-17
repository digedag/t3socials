<?php

return [
    'web_T3socialsM1' => [
        'parent' => 'web',
        'position' => ['bottom'],
        'access' => 'user',
        'workspaces' => '*',
        'iconIdentifier' => 'ext-t3socials-ext-default',
        'path' => '/module/web/t3socials',
        'labels' => 'LLL:EXT:t3socials/Resources/Private/Language/locallang_mod.xlf',
        'extensionName' => 'T3socials',
        'routes' => [
            '_default' => [
                'target' => DMK\T3socials\Backend\Controller\Communicator::class,
            ],
        ],
    ],
    'web_T3socialsM1_communicator' => [
        'parent' => 'web_T3socialsM1',
        'access' => 'user,group',
        'workspaces' => '*',
        'iconIdentifier' => 'ext-t3socials-ext-default',
        'path' => '/module/web/t3sports/competition',
        'labels' => [
            'title' => 'LLL:EXT:t3socials/Resources/Private/Language/locallang.xlf:mod_competition',
        ],
        'routes' => [
            '_default' => [
                'target' => DMK\T3socials\Backend\Controller\Communicator::class.'::main',
            ],
        ],
        'moduleData' => [
            'langFiles' => [],
            'pages' => '0',
            'depth' => 0,
        ],
    ],
    'web_T3socialsM1_trigger' => [
        'parent' => 'web_T3socialsM1',
        'access' => 'user,group',
        'workspaces' => '*',
        'iconIdentifier' => 'ext-t3socials-ext-default',
        'path' => '/module/web/t3sports/trigger',
        'labels' => [
            'title' => 'LLL:EXT:t3socials/Resources/Private/Language/locallang.xlf:match_ticker',
        ],
        'routes' => [
            '_default' => [
                'target' => DMK\T3socials\Backend\Controller\Trigger::class.'::main',
            ],
        ],
        'moduleData' => [
            'langFiles' => [],
            'pages' => '0',
            'depth' => 0,
        ],
    ],
];

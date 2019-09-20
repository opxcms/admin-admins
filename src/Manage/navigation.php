<?php

return [
    'items' => [
        'administrators' => [
            'caption' => 'navigation.settings_administrators',
            'section' => 'system/settings',
            'route' => 'admin_admins::admins_list',
            'permission' => 'admin_admins::list',
        ],
    ],

    'routes' => [
        'admin_admins::admins_list' => [
            'route' => '/admins',
            'loader' => 'manage/api/module/admin_admins/admins_list',
        ],
        'admin_admins::admins_add' => [
            'route' => '/admins/add',
            'loader' => 'manage/api/module/admin_admins/admin_edit/add',
        ],
        'admin_admins::admins_edit' => [
            'route' => '/admins/edit/:id',
            'loader' => 'manage/api/module/admin_admins/admin_edit/edit',
        ],
    ],
];
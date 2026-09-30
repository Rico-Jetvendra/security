<?php
    return [
        'menu' => [
            [
                'label'         => 'Dashboard',
                'route'         => 'web.index',
                'icon'          => 'bi-speedometer2',
                'permission'    => 'home'
            ],
            [
                'label'         => 'Security',
                'icon'          => 'bi-shield-lock',
                'children' => [
                    ['label' => 'Application', 'route' => 'web.application.index', 'permission' => 'security.application'],
                    ['label' => 'Role', 'route' => 'web.role.index', 'permission' => 'security.role'],
                    ['label' => 'Permission', 'route' => 'web.permission.index', 'permission' => 'security.permission'],
                    ['label' => 'Role Permission', 'route' => 'web.role-permission.index', 'permission' => 'security.role_permission'],
                    ['label' => 'User', 'route' => 'web.user.index', 'permission' => 'security.user'],
                    ['label' => 'User Role', 'route' => 'web.user-role.index', 'permission' => 'security.user_role'],
                    ['label' => 'Teams', 'route' => 'web.teams.index', 'permission' => 'security.teams'],
                    ['label' => 'Member', 'route' => 'web.member.index', 'permission' => 'security.member'],
                ]
            ],
            [
                'label'         => 'Monitoring',
                'icon'          => 'bi-activity',
                'children' => [
                    ['label' => 'Login History', 'route' => 'web.login-history.index', 'permission' => 'monitoring.login_history'],
                    ['label' => 'Activity Logs', 'route' => 'web.activity-logs.index', 'permission' => 'monitoring.activity_logs'],
                ]
            ],
        ],
        'app_type' => [
            [
                'id' => '1',
                'name' => 'Web Application'
            ],
            [
                'id' => '2',
                'name' => 'Mobile Application'
            ],
        ]
    ];
?>

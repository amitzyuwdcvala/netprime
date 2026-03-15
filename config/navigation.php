<?php

return [

    'sidebar' => [
        [
            'label' => 'Dashboard',
            'link' => 'admin.dashboard',
            'icon' => 'fa-home',
            'is_visible' => true,
        ],
        [
            'label' => 'Payment Gateways',
            'link' => 'admin.gateways.index',
            'icon' => 'fa-cog',
            'is_visible' => true,
        ],
        [
            'label' => 'Users',
            'link' => 'admin.users.index',
            'icon' => 'fa-users',
            'is_visible' => true,
        ],
        [
            'label' => 'Subscriptions',
            'link' => 'admin.plans.index',
            'icon' => 'fa-crown',
            'is_visible' => true,
        ],
        [
            'label' => 'Payments',
            'link' => 'admin.payments.index',
            'icon' => 'fa-money-bill-wave',
            'is_visible' => true,
        ],
        [
            'label' => 'Config',
            'link' => 'admin.config.index',
            'icon' => 'fa-sliders-h',
            'is_visible' => true,
        ],
    ],

];

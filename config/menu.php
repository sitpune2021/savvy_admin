<?php

return [
    [
        'type' => 'section',
        'label' => 'Menu',
        'roles' => ['admin', 'vendor', 'plant-manager'],
    ],
    [
        'label' => 'Dashboards',
        'url' => '/',
        'icon' => 'ri-dashboard-2-line',
        'roles' => ['admin', 'vendor', 'plant-manager'],
    ],

    [
        'type' => 'section',
        'label' => 'Customers',
        'roles' => ['admin', 'vendor', 'plant-manager'],
    ],
    [
        'label' => 'Plants',
        'url' => 'plant',
        'icon' => 'ri-building-2-line',
        'roles' => ['admin', 'vendor'],
    ],
    [
        'label' => 'Routes',
        'url' => 'route',
        'icon' => 'ri-building-2-line',
        'roles' => ['admin', 'vendor', 'plant-manager'],
    ],
    [
        'label' => 'Drivers',
        'url' => 'driver',
        'icon' => 'ri-dashboard-2-line',
        'roles' => ['admin', 'vendor', 'plant-manager'],
    ],
    [
        'label' => 'Products',
        'url' => 'product',
        'icon' => 'ri-dashboard-2-line',
        'roles' => ['admin'],
    ],
    [
        'label' => 'Customers',
        'url' => 'customer',
        'icon' => 'ri-dashboard-2-line',
        'roles' => ['admin','vendor', 'plant-manager'],
    ],
    [
        'label' => 'Dispensary',
        'url' => 'dispensary',
        'icon' => 'ri-dashboard-2-line',
        'roles' => ['admin'],
    ],

    [
        'type' => 'section',
        'label' => 'Purchases',
        'roles' => ['admin' ,'vendor', 'plant-manager'],
    ],
    [
        'label' => 'Orders',
        'url' => 'order',
        'icon' => 'ri-dashboard-2-line',
        'roles' => ['admin' ,'vendor', 'plant-manager'],
    ],
    [
        'label' => 'Orders Request',
        'url' => 'request-order',
        'icon' => 'ri-dashboard-2-line',
        'roles' => ['admin'],
    ],
    [
        'label' => 'Maintenance',
        'url' => 'maintenance',
        'icon' => 'ri-dashboard-2-line',
        'roles' => ['admin'],
    ],

    [
        'type' => 'section',
        'label' => 'Vendors',
        'roles' => ['admin'],
    ],
    [
        'label' => 'Vendor',
        'url' => 'vendor',
        'icon' => 'ri-dashboard-2-line',
        'roles' => ['admin'],
    ]
];

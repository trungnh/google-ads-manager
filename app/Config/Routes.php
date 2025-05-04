<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('PublicPages');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// Migration route - chỉ dùng trong môi trường development
if (ENVIRONMENT === 'development') {
    $routes->get('migrate', 'Migrate::index');
}

// Public routes
$routes->get('/', 'PublicPages::index');
$routes->get('homepage', 'PublicPages::index');
$routes->get('privacy-policy', 'PublicPages::privacyPolicy');
$routes->get('terms', 'PublicPages::terms');

// Auth routes (without auth filter)
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attemptLogin');
$routes->get('logout', 'Auth::logout');

// Ads Accounts routes
$routes->get('ads-accounts', 'AdsAccounts::index');
$routes->get('ads-accounts/create', 'AdsAccounts::create');
$routes->post('ads-accounts/store', 'AdsAccounts::store');

// Protected routes (with auth filter)
$routes->group('', ['filter' => 'auth'], function($routes) {
    // Dashboard route
    $routes->get('dashboard', 'Dashboard::index');
    
    // Các route cho role user, admin, superadmin
    
    // Profile routes
    $routes->get('profile', 'UserProfile::index');
    $routes->post('profile/update', 'UserProfile::update');

    // Settings routes
    $routes->get('settings', 'Settings::index');
    $routes->post('settings/update', 'Settings::update');
    
    // Sync Ads routes
    $routes->get('syncads', 'SyncAds::index');
    $routes->post('syncads/syncaccounts', 'SyncAds::syncAccounts');
    
    // Ads Accounts routes
    $routes->get('adsaccounts', 'AdsAccounts::index');
    
    // Campaign routes
    $routes->get('campaigns', 'Campaigns::index'); 
    $routes->get('campaigns/index/(:segment)', 'Campaigns::index/$1');
    $routes->get('campaigns/(:segment)', 'Campaigns::index/$1');
    $routes->get('campaigns/load/(:segment)', 'Campaigns::loadCampaigns/$1');
    $routes->post('campaigns/toggleStatus/(:segment)/(:segment)', 'Campaigns::toggleStatus/$1/$2');
    $routes->post('campaigns/updateTarget/(:segment)/(:segment)', 'Campaigns::updateTarget/$1/$2');
    $routes->post('campaigns/updateBudget/(:segment)/(:segment)', 'Campaigns::updateBudget/$1/$2');

    // Google Auth routes
    $routes->get('google/oauth', 'GoogleAuth::oauth');
    $routes->get('google/callback', 'GoogleAuth::callback');

    // Optimize Logs route
    $routes->get('optimize-logs', 'OptimizeLogs::index');
    $routes->get('adsaccounts/settings/(:num)', 'AdsAccountSettings::index/$1');
    $routes->post('adsaccounts/settings/update/(:num)', 'AdsAccountSettings::update/$1');
    $routes->post('adsaccounts/delete/(:num)', 'AdsAccounts::delete/$1');

    // Route chỉ dành cho role admin và superadmin
    $routes->group('', ['filter' => 'role:admin,superadmin'], function($routes) {
        $routes->post('campaigns/updateCFLC/(:segment)/(:segment)', 'Campaigns::updateCFLC/$1/$2');
        // Campaign Schedules Routes
        $routes->get('campaignschedules/(:segment)', 'CampaignSchedules::index/$1', ['filter' => 'auth']);
        $routes->get('campaignschedules/(:segment)/create', 'CampaignSchedules::create/$1', ['filter' => 'auth']);
        $routes->post('campaignschedules/(:segment)/create', 'CampaignSchedules::create/$1', ['filter' => 'auth']);
        $routes->get('campaignschedules/(:segment)/edit/(:num)', 'CampaignSchedules::edit/$1/$2', ['filter' => 'auth']);
        $routes->post('campaignschedules/(:segment)/edit/(:num)', 'CampaignSchedules::edit/$1/$2', ['filter' => 'auth']);
        $routes->get('campaignschedules/(:segment)/delete/(:num)', 'CampaignSchedules::delete/$1/$2', ['filter' => 'auth']);
    });

    // Route chỉ dành cho role superadmin - quản lý người dùng
    $routes->group('', ['filter' => 'role:superadmin'], function($routes) {
        $routes->get('users', 'Users::index');
        $routes->get('users/create', 'Users::create');
        $routes->post('users/store', 'Users::store');
        $routes->get('users/edit/(:num)', 'Users::edit/$1');
        $routes->post('users/update/(:num)', 'Users::update/$1');
        $routes->get('users/delete/(:num)', 'Users::delete/$1');
        $routes->get('optimize-logs/view/(:num)', 'OptimizeLogs::view/$1');
        $routes->get('reports/view/(:num)', 'Reports::view/$1');
    });
});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 */

if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
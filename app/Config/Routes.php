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

// Public routes
$routes->get('/', 'PublicPages::index');
$routes->get('homepage', 'PublicPages::index');
$routes->get('privacy-policy', 'PublicPages::privacyPolicy');
$routes->get('terms', 'PublicPages::terms');

// Auth routes (without auth filter)
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attemptLogin');
$routes->get('register', 'Auth::register');
$routes->post('register', 'Auth::attemptRegister');
$routes->get('logout', 'Auth::logout');

// Protected routes (with auth filter)
$routes->group('', ['filter' => 'auth'], function($routes) {
    // Dashboard route
    $routes->get('dashboard', 'Dashboard::index');
    
    // Settings routes
    $routes->get('settings', 'Settings::index');
    $routes->post('settings/update', 'Settings::update');
    
    // Sync Ads routes
    $routes->get('syncads', 'SyncAds::index');
    $routes->post('syncads/syncaccounts', 'SyncAds::syncAccounts');
    
    // Ads Accounts routes
    $routes->get('adsaccounts', 'AdsAccounts::index');
    $routes->get('adsaccounts/settings/(:num)', 'AdsAccountSettings::index/$1');
    $routes->post('adsaccounts/settings/update/(:num)', 'AdsAccountSettings::update/$1');
    
    // Campaign routes
    $routes->get('campaigns/index/(:segment)', 'Campaigns::index/$1');
    $routes->get('campaigns/(:segment)', 'Campaigns::index/$1');
    $routes->get('campaigns/load/(:segment)', 'Campaigns::loadCampaigns/$1');
    $routes->post('campaigns/toggleStatus/(:segment)/(:segment)', 'Campaigns::toggleStatus/$1/$2');
    $routes->post('campaigns/updateTarget/(:segment)/(:segment)', 'Campaigns::updateTarget/$1/$2');
});

// Google Auth routes (without auth filter)
$routes->get('google/oauth', 'GoogleAuth::oauth');
$routes->get('google/callback', 'GoogleAuth::callback');

// Optimize Logs route
$routes->get('optimize-logs', 'OptimizeLogs::index');

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 */

if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
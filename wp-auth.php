<?php
/*
Plugin Name: Auth API for WordPress
Description: Exposes an API endpoint to authenticate users via username and password.
Version: 1.0
Author: Ryan Chouinard
*/

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/functions.php';

add_action('rest_api_init', function () {
    /**
     * Registers a REST API endpoint for user login authentication.
     * This function sets up a POST route under the namespace 'auth-api/v1' and the endpoint name 'login'.
     * The callback function `auth_api_login` will handle the incoming requests to this endpoint.
     * 
     * @return void
     */
    register_rest_route('auth-api/v1', '/login', [
        'methods' => 'POST',
        'callback' => 'auth_api_login',
        'permission_callback' => '__return_true',
    ]);

    /**
     * Registers a REST API endpoint to retrieve user data for authenticated users.
     * This function sets up a GET route under the namespace 'auth-api/v1' and the endpoint name 'me'.
     * The callback function `auth_api_get_user_data` will handle the incoming requests to this endpoint.
     * 
     * @return void
     */
    register_rest_route('auth-api/v1', '/me', [
        'methods' => 'GET',
        'callback' => 'auth_api_get_user_data',
        'permission_callback' => '__return_true',
    ]);
});

add_action('admin_init', function () {
    register_setting('auth-api-options-group', 'jwt_auth_secret_key', [
        'type' => 'string',
        'description' => 'The secret key used to sign the JWTs.',
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
});

add_action('admin_menu', function () {
    add_options_page(
        __('WP Auth API Settings', 'auth-api'),
        __('WP Auth API', 'auth-api'),
        'manage_options',
        'auth-api-options-group',
        'auth_api_options_page',
    );
});

<?php

return [
    /*
    |--------------------------------------------------------------------------
    | LDAP Authentication Active Directory Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for authenticating users against PT Semen Indonesia (SIG)
    | Active Directory LDAP server.
    |
    */

    'enabled' => env('LDAP_ENABLED', true),

    'host' => env('LDAP_HOST', '10.60.12.44'),

    'port' => (int) env('LDAP_PORT', 389),

    'domain' => env('LDAP_DOMAIN', 'sig.id'),

    'base_dn' => env('LDAP_BASE_DN', 'DC=sig,DC=id'),

    'bind_user' => env('LDAP_BIND_USER', ''),

    'bind_password' => env('LDAP_BIND_PASSWORD', ''),

    // Timeout in seconds for connection attempt
    'timeout' => (int) env('LDAP_TIMEOUT', 3),

    'ssl' => env('LDAP_SSL', false),

    'tls' => env('LDAP_TLS', false),
];

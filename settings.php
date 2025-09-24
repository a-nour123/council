<?php 
return [
    'strict' => env('SAML_STRICT', true),
    'debug' => env('SAML_DEBUG', true),

    'sp' => [
        'entityId' => env('SAML_SP_ENTITYID'),
        'assertionConsumerService' => [
            'url' => env('SAML_SP_ACS_URL'),
            'binding' => env('SAML_SP_ACS_BINDING'),
        ],
        'singleLogoutService' => [
            'url' => env('SAML_SP_SLO_URL'),
            'binding' => env('SAML_SP_SLO_BINDING'),
        ],
        'x509cert' => env('SAML_SP_X509CERT'),
        'privateKey' => env('SAML_SP_PRIVATEKEY'),
    ],

    'idp' => [
        'entityId' => env('SAML_IDP_ENTITYID'),
        'singleSignOnService' => [
            'url' => env('SAML_IDP_SSO_URL'),
            'binding' => env('SAML_IDP_SSO_BINDING'),
        ],
        'singleLogoutService' => [
            'url' => env('SAML_IDP_SLO_URL'),
            'binding' => env('SAML_IDP_SLO_BINDING'),
        ],
        'x509cert' => env('SAML_IDP_X509CERT'),
    ],
];

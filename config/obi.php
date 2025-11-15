<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Agent Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for the AI agent behavior and identity.
    | The nickname is used when the agent introduces itself or signs messages.
    |
    */

    'agent' => [
        'nickname' => env('OBI_AGENT_NICKNAME', 'Obi')
    ],

    /*
    |--------------------------------------------------------------------------
    | Gemini API Key
    |--------------------------------------------------------------------------
    |
    | Your Gemini API key for authentication with Google's Gemini AI service.
    | You can obtain this from https://makersuite.google.com/app/apikey
    |
    */

    'api_key' => env('GEMINI_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Gemini Model
    |--------------------------------------------------------------------------
    |
    | The Gemini model to use for AI interactions.
    | Available models: gemini-2.5-flash, gemini-pro, etc.
    |
    */

    'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),

    /*
    |--------------------------------------------------------------------------
    | Declaration Pools
    |--------------------------------------------------------------------------
    |
    | Paths where declaration files are stored. You can add multiple paths
    | to organize declarations by module or feature.
    |
    */

    'declaration_pools' => [
        base_path('declarations'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable logging of AI prompts and responses for debugging.
    |
    */

    'logging' => [
        'enabled' => env('OBI_LOGGING_ENABLED', false),
        'channel' => env('OBI_LOG_CHANNEL', 'stack'),
    ],

];

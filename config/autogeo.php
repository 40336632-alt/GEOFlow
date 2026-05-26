<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AutoGEO Service Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the AutoGEO microservice integration.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL of the AutoGEO microservice.
    |
    */
    'base_url' => env('AUTOGEO_BASE_URL', 'http://localhost:5000'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | Timeout in seconds for AutoGEO API requests.
    |
    */
    'timeout' => env('AUTOGEO_TIMEOUT', 300),

    /*
    |--------------------------------------------------------------------------
    | Max Retries
    |--------------------------------------------------------------------------
    |
    | Maximum number of retry attempts for failed requests.
    |
    */
    'retries' => env('AUTOGEO_RETRIES', 3),

    /*
    |--------------------------------------------------------------------------
    | Default Dataset
    |--------------------------------------------------------------------------
    |
    | The default dataset/domain for content optimization.
    | Options: default, medical, ecommerce, research
    |
    */
    'default_dataset' => env('AUTOGEO_DEFAULT_DATASET', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Default Engine
    |--------------------------------------------------------------------------
    |
    | The default LLM engine for content rewriting.
    | Options: gemini, openai, anthropic
    |
    */
    'default_engine' => env('AUTOGEO_DEFAULT_ENGINE', 'gemini'),

    /*
    |--------------------------------------------------------------------------
    | Enable Auto-Optimization
    |--------------------------------------------------------------------------
    |
    | When enabled, all generated content will be automatically optimized
    | using AutoGEO before being saved.
    |
    */
    'auto_optimize' => env('AUTOGEO_AUTO_OPTIMIZE', false),

    /*
    |--------------------------------------------------------------------------
    | Enable Evaluation
    |--------------------------------------------------------------------------
    |
    | When enabled, GEO scores will be calculated for optimized content.
    |
    */
    'enable_evaluation' => env('AUTOGEO_ENABLE_EVALUATION', true),

    /*
    |--------------------------------------------------------------------------
    | Dataset Mappings
    |--------------------------------------------------------------------------
    |
    | Map task categories to AutoGEO datasets.
    |
    */
    'dataset_mappings' => [
        'medical' => 'medical',
        'health' => 'medical',
        'healthcare' => 'medical',
        'ecommerce' => 'ecommerce',
        'product' => 'ecommerce',
        'shopping' => 'ecommerce',
        'research' => 'research',
        'academic' => 'research',
        'science' => 'research',
    ],

    /*
    |--------------------------------------------------------------------------
    | Engine Mappings
    |--------------------------------------------------------------------------
    |
    | Map GEOFlow AI models to AutoGEO engines.
    |
    */
    'engine_mappings' => [
        'gemini' => 'gemini',
        'google' => 'gemini',
        'openai' => 'openai',
        'gpt' => 'openai',
        'anthropic' => 'anthropic',
        'claude' => 'anthropic',
    ],

];

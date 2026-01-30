<?php
return [

    'locations' => [
        'base_table'        => 'locations',
        'translation_table' => 'location_translations',
        'foreign_key'       => 'location_id',
        'fields'            => ['name'],  // translation fields
        'base_fields'       => ['parent_id'], // extra fields in base table
    ],


    'categories' => [
        'base_table'        => 'categories',
        'translation_table' => 'category_translations',
        'foreign_key'       => 'category_id',
        'fields'            => ['name'],
        'base_fields'       => ['image_url'],
    ],

    'fuels' => [
        'base_table'        => 'fuels',
        'translation_table' => 'fuel_translations',
        'foreign_key'       => 'fuel_id',
        'fields'            => ['name'],
        'base_fields'       => [],
    ],


    'transmissions' => [
        'base_table'        => 'transmissions',
        'translation_table' => 'transmission_translations',
        'foreign_key'       => 'transmission_id',
        'fields'            => ['name'],
        'base_fields'       => [],
    ],


    'drivetrains' => [
        'base_table'        => 'drivetrains',
        'translation_table' => 'drivetrain_translations',
        'foreign_key'       => 'drivetrain_id',
        'fields'            => ['name'],
        'base_fields'       => [],
    ],

    'conditions' => [
        'base_table'        => 'conditions',
        'translation_table' => 'condition_translations',
        'foreign_key'       => 'condition_id',
        'fields'            => ['name'],
        'base_fields'       => [],
    ],

    'makes' => [
        'base_table'        => 'makes',
        'translation_table' => 'make_translations',
        'foreign_key'       => 'make_id',
        'fields'            => ['name'],
        'base_fields'       => ['category_id'],
    ],


    'car_models' => [
        'base_table'        => 'car_models',
        'translation_table' => 'car_model_translations',
        'foreign_key'       => 'car_model_id',
        'fields'            => ['name'],
        'base_fields'       => ['make_id'],
    ],


    'engines' => [
        'base_table'        => 'engines',
        'translation_table' => 'engine_translations',
        'foreign_key'       => 'engine_id',
        'fields'            => ['name'],
        'base_fields'       => [],
    ],


    'colors' => [
        'base_table'        => 'colors',
        'translation_table' => 'color_translations',
        'foreign_key'       => 'color_id',
        'fields'            => ['name'],
        'base_fields'       => ['code'],
    ],



    'currencies' => [
        'base_table'        => 'currencies',
        'translation_table' => 'currency_translations',
        'foreign_key'       => 'currency_id',
        'fields'            => ['name'],
        'base_fields'       => ['code'],
    ],


    'engine_sizes' => [
        'base_table'        => 'engine_sizes',
        'translation_table' => 'engine_size_translations',
        'foreign_key'       => 'engine_size_id',
        'fields'            => ['name'],
        'base_fields'       => ['value'],
    ],


    'driver_types' => [
        'base_table'        => 'driver_types',
        'translation_table' => 'driver_type_translations',
        'foreign_key'       => 'driver_type_id',
        'fields'            => ['name'],
        'base_fields'       => [],
    ],


    'gas_equipments' => [
        'base_table'        => 'gas_equipments',
        'translation_table' => 'gas_equipment_translations',
        'foreign_key'       => 'gas_equipment_id',
        'fields'            => ['name'],
        'base_fields'       => [],
    ],

    'wheel_sizes' => [
        'base_table'        => 'wheel_sizes',
        'translation_table' => 'wheel_size_translations',
        'foreign_key'       => 'wheel_size_id',
        'fields'            => ['name'],
        'base_fields'       => [],
    ],

    'headlights' => [
        'base_table'        => 'headlights',
        'translation_table' => 'headlight_translations',
        'foreign_key'       => 'headlight_id',
        'fields'            => ['name'],
        'base_fields'       => [],
    ],


    'interior_colors' => [
        'base_table'        => 'interior_colors',
        'translation_table' => 'interior_color_translations',
        'foreign_key'       => 'interior_color_id',
        'fields'            => ['name'],
        'base_fields'       => [],
    ],


    'interior_materials' => [
        'base_table'        => 'interior_materials',
        'translation_table' => 'interior_material_translations',
        'foreign_key'       => 'interior_material_id',
        'fields'            => ['name'],
        'base_fields'       => [],
    ],


    'steering_wheels' => [
        'base_table'        => 'steering_wheels',
        'translation_table' => 'steering_wheel_translations',
        'foreign_key'       => 'steering_wheel_id',
        'fields'            => ['name'],
        'base_fields'       => [],
    ],



    'packages' => [
        'base_table'        => 'packages',
        'translation_table' => 'package_translations',
        'foreign_key'       => 'package_id',
        'fields'            => ['name', 'description'],
        'base_fields'       => ['key', 'price', 'duration_days', 'max_active_listings',  'included_featured_days', 'top_listings_count',  'is_active'],
    ],


    'advertisements' => [
        'base_table'        => 'advertisements',
        'translation_table' => 'advertisement_translations',
        'foreign_key'       => 'advertisement_id',
        'fields'            => ['name'],
        'base_fields'       => ['key', 'price', 'duration_days', 'is_active'],
    ],

];

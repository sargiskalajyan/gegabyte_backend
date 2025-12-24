<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    protected $table = 'listings';
    protected $guarded = ['id'];

    // Add the casts property
    protected $casts = [
        'exchange' => 'boolean',
    ];

    protected $appends = [
        'make_name',
        'model_name',
        'fuel_name',
        'transmission_name',
        'drivetrain_name',
        'condition_name',
        'color_name',
        'driver_type_name',
        'category_name',
        'location_name',
        'gas_equipment_name', // Added new accessor
        'wheel_size_name',    // Added new accessor
        'headlight_name',     // Added new accessor
        'interior_color_name',// Added new accessor
        'interior_material_name',// Added new accessor
        'steering_wheel_name',// Added new accessor
    ];

    /* ----------------------- RELATIONS ----------------------- */

    public function photos()
    {
        return $this->hasMany(ListingPhoto::class);
    }

    public function make()
    {
        return $this->belongsTo(Make::class);
    }

    public function carModel()
    {
        return $this->belongsTo(CarModel::class, 'car_model_id');
    }

    public function fuel()
    {
        return $this->belongsTo(Fuel::class);
    }

    public function transmission()
    {
        return $this->belongsTo(Transmission::class);
    }

    public function drivetrain()
    {
        return $this->belongsTo(Drivetrain::class);
    }

    public function condition()
    {
        return $this->belongsTo(Condition::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function driverType()
    {
        return $this->belongsTo(DriverType::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // New relationships based on the migration lines
    public function gasEquipment()
    {
        return $this->belongsTo(GasEquipment::class);
    }

    public function wheelSize()
    {
        return $this->belongsTo(WheelSize::class);
    }

    public function headlight()
    {
        return $this->belongsTo(Headlight::class);
    }

    public function interiorColor()
    {
        return $this->belongsTo(InteriorColor::class);
    }

    public function interiorMaterial()
    {
        return $this->belongsTo(InteriorMaterial::class);
    }

    public function steeringWheel()
    {
        return $this->belongsTo(SteeringWheel::class);
    }


    /* ------------------ ACCESSORS (JOIN aware) ------------------ */

    private function joinOrRelation($joinValue, $relation, $attr)
    {
        if ($joinValue) return $joinValue;
        return $this->$relation?->translation?->$attr;
    }

    public function getMakeNameAttribute()
    {
        return $this->joinOrRelation($this->attributes['make_name'] ?? null, 'make', 'name');
    }

    public function getModelNameAttribute()
    {
        return $this->joinOrRelation($this->attributes['model_name'] ?? null, 'carModel', 'name');
    }

    public function getFuelNameAttribute()
    {
        return $this->joinOrRelation($this->attributes['fuel_name'] ?? null, 'fuel', 'name');
    }

    public function getTransmissionNameAttribute()
    {
        return $this->joinOrRelation($this->attributes['transmission_name'] ?? null, 'transmission', 'name');
    }

    public function getDrivetrainNameAttribute()
    {
        return $this->joinOrRelation($this->attributes['drivetrain_name'] ?? null, 'drivetrain', 'name');
    }

    public function getConditionNameAttribute()
    {
        return $this->joinOrRelation($this->attributes['condition_name'] ?? null, 'condition', 'name');
    }

    public function getColorNameAttribute()
    {
        return $this->joinOrRelation($this->attributes['color_name'] ?? null, 'color', 'name');
    }

    public function getDriverTypeNameAttribute()
    {
        return $this->joinOrRelation($this->attributes['driver_type_name'] ?? null, 'driverType', 'name');
    }

    public function getCategoryNameAttribute()
    {
        return $this->joinOrRelation($this->attributes['category_name'] ?? null, 'category', 'name');
    }

    public function getLocationNameAttribute()
    {
        return $this->joinOrRelation($this->attributes['location_name'] ?? null, 'location', 'name');
    }

    // New accessors for the new relationships
    public function getGasEquipmentNameAttribute()
    {
        return $this->joinOrRelation($this->attributes['gas_equipment_name'] ?? null, 'gasEquipment', 'name');
    }

    public function getWheelSizeNameAttribute()
    {
        return $this->joinOrRelation($this->attributes['wheel_size_name'] ?? null, 'wheelSize', 'name');
    }

    public function getHeadlightNameAttribute()
    {
        return $this->joinOrRelation($this->attributes['headlight_name'] ?? null, 'headlight', 'name');
    }

    public function getInteriorColorNameAttribute()
    {
        return $this->joinOrRelation($this->attributes['interior_color_name'] ?? null, 'interiorColor', 'name');
    }

    public function getInteriorMaterialNameAttribute()
    {
        return $this->joinOrRelation($this->attributes['interior_material_name'] ?? null, 'interiorMaterial', 'name');
    }

    public function getSteeringWheelNameAttribute()
    {
        return $this->joinOrRelation($this->attributes['steering_wheel_name'] ?? null, 'steeringWheel', 'name');
    }


    /**
     * @return void
     */
    public function loadTranslationAttributes()
    {
        $lang = app()->getLocale();

        $translated = self::query()
            ->select([
                'listings.*',
                'make_trans.name AS make_name',
                'model_trans.name AS model_name',
                'fuel_trans.name AS fuel_name',
                'trans_trans.name AS transmission_name',
                'drive_trans.name AS drivetrain_name',
                'cond_trans.name AS condition_name',
                'color_trans.name AS color_name',
                'driver_trans.name AS driver_type_name',
                'cat_trans.name AS category_name',
                'loc_trans.name AS location_name',
                'gas_eq_trans.name AS gas_equipment_name', // Added new select
                'wheel_s_trans.name AS wheel_size_name',    // Added new select
                'headlight_trans.name AS headlight_name',   // Added new select
                'int_color_trans.name AS interior_color_name',// Added new select
                'int_mat_trans.name AS interior_material_name',// Added new select
                'steer_w_trans.name AS steering_wheel_name',// Added new select
            ])
            // MAKE
            ->leftJoin('make_translations AS make_trans', 'make_trans.make_id', '=', 'listings.make_id')
            ->leftJoin('languages AS lmake', 'lmake.id', '=', 'make_trans.language_id')
            ->where('lmake.code', $lang)
            // CAR MODEL
            ->leftJoin('car_model_translations AS model_trans', 'model_trans.car_model_id', '=', 'listings.car_model_id')
            ->leftJoin('languages AS lmodel', 'lmodel.id', '=', 'model_trans.language_id')
            ->where('lmodel.code', $lang)
            // FUEL
            ->leftJoin('fuel_translations AS fuel_trans', 'fuel_trans.fuel_id', '=', 'listings.fuel_id')
            ->leftJoin('languages AS lfuel', 'lfuel.id', '=', 'fuel_trans.language_id')
            ->where('lfuel.code', $lang)
            // TRANSMISSION
            ->leftJoin('transmission_translations AS trans_trans', 'trans_trans.transmission_id', '=', 'listings.transmission_id')
            ->leftJoin('languages AS ltrans', 'ltrans.id', '=', 'trans_trans.language_id')
            ->where('ltrans.code', $lang)
            // DRIVETRAIN
            ->leftJoin('drivetrain_translations AS drive_trans', 'drive_trans.drivetrain_id', '=', 'listings.drivetrain_id')
            ->leftJoin('languages AS ldrive', 'ldrive.id', '=', 'drive_trans.language_id')
            ->where('ldrive.code', $lang)
            // CONDITION
            ->leftJoin('condition_translations AS cond_trans', 'cond_trans.condition_id', '=', 'listings.condition_id')
            ->leftJoin('languages AS lcond', 'lcond.id', '=', 'cond_trans.language_id')
            ->where('lcond.code', $lang)
            // COLOR
            ->leftJoin('color_translations AS color_trans', 'color_trans.color_id', '=', 'listings.color_id')
            ->leftJoin('languages AS lcolor', 'lcolor.id', '=', 'color_trans.language_id')
            ->where('lcolor.code', $lang)
            // DRIVER TYPE
            ->leftJoin('driver_type_translations AS driver_trans', 'driver_trans.driver_type_id', '=', 'listings.driver_type_id')
            ->leftJoin('languages AS ldriver', 'ldriver.id', '=', 'driver_trans.language_id')
            ->where('ldriver.code', $lang)
            // CATEGORY
            ->leftJoin('category_translations AS cat_trans', 'cat_trans.category_id', '=', 'listings.category_id')
            ->leftJoin('languages AS lcat', 'lcat.id', '=', 'cat_trans.language_id')
            ->where('lcat.code', $lang)
            // LOCATION
            ->leftJoin('location_translations AS loc_trans', 'loc_trans.location_id', '=', 'listings.location_id')
            ->leftJoin('languages AS lloc', 'lloc.id', '=', 'loc_trans.language_id')
            ->where('lloc.code', $lang)
            // GAS EQUIPMENT (New)
            ->leftJoin('gas_equipment_translations AS gas_eq_trans', 'gas_eq_trans.gas_equipment_id', '=', 'listings.gas_equipment_id')
            ->leftJoin('languages AS lgas_eq', 'lgas_eq.id', '=', 'gas_eq_trans.language_id')
            ->where('lgas_eq.code', $lang)
            // WHEEL SIZE (New)
            ->leftJoin('wheel_size_translations AS wheel_s_trans', 'wheel_s_trans.wheel_size_id', '=', 'listings.wheel_size_id')
            ->leftJoin('languages AS lwheel_s', 'lwheel_s.id', '=', 'wheel_s_trans.language_id')
            ->where('lwheel_s.code', $lang)
            // HEADLIGHT (New)
            ->leftJoin('headlight_translations AS headlight_trans', 'headlight_trans.headlight_id', '=', 'listings.headlight_id')
            ->leftJoin('languages AS lheadlight', 'lheadlight.id', '=', 'headlight_trans.language_id')
            ->where('lheadlight.code', $lang)
            // INTERIOR COLOR (New)
            ->leftJoin('interior_color_translations AS int_color_trans', 'int_color_trans.interior_color_id', '=', 'listings.interior_color_id')
            ->leftJoin('languages AS lint_color', 'lint_color.id', '=', 'int_color_trans.language_id')
            ->where('lint_color.code', $lang)
            // INTERIOR MATERIAL (New)
            ->leftJoin('interior_material_translations AS int_mat_trans', 'int_mat_trans.interior_material_id', '=', 'listings.interior_material_id')
            ->leftJoin('languages AS lint_mat', 'lint_mat.id', '=', 'int_mat_trans.language_id')
            ->where('lint_mat.code', $lang)
            // STEERING WHEEL (New)
            ->leftJoin('steering_wheel_translations AS steer_w_trans', 'steer_w_trans.steering_wheel_id', '=', 'listings.steering_wheel_id')
            ->leftJoin('languages AS lsteer_w', 'lsteer_w.id', '=', 'steer_w_trans.language_id')
            ->where('lsteer_w.code', $lang)

            ->where('listings.id', $this->id)
            ->first();

        if ($translated) {
            foreach ($translated->toArray() as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }

}

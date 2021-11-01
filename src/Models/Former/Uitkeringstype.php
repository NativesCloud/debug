<?php

namespace Vng\EvaCore\Models\Former;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Uitkeringstype extends Model
{
    use SoftDeletes;

    protected $table = 'uitkeringstype';

    /**
     *  Setup model event hooks
     */
    public static function boot()
    {
        parent::boot();

        self::saving(function ($model) {
            if (request()->user()->is_admin != true) {
                $model->owner_id = request()->user()->role->access_id;
                $model->owner_type = request()->user()->role->access_type;
            }
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function instrument()
    {
        return $this->belongsToMany(Instrument::class, 'uitkeringstype_instrument', 'uitkeringstype_id', 'instrument_id')->using(UitkeringstypeInstrument::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}

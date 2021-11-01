<?php

namespace Vng\EvaCore\Models;

use Vng\EvaCore\Enums\ContactTypeEnum;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

class Contactables extends MorphPivot
{
    public $incrementing = true;
    protected $table = 'contactables';

    protected $attributes = [
        'type'
    ];

    protected $fillable = [
        'type'
    ];

    public function setTypeAttribute($value)
    {
        if (is_null($value)) {
            $this->attributes['type'] = null;
            return;
        }
        $this->attributes['type'] = (new ContactTypeEnum($value))->getKey();
    }

    public function getTypeAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }
        if(in_array($value, ContactTypeEnum::keys())) {
            return ContactTypeEnum::$value();
        }
        return $this->attributes['type'];
    }

    public function getRawTypeAttribute()
    {
        return $this->attributes['type'];
    }
}


<?php

namespace Vng\EvaCore\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientCharacteristicResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->updated_at,

            'name' => $this->name,

            'instruments' => InstrumentResource::collection($this->whenLoaded('instruments'))
        ];
    }
}

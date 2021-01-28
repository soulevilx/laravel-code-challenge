<?php

namespace App\Http\Resources;

use App\Models\DebitCard;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DebitCard
 */
class DebitCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'type' => $this->type,
            'expiration_date' => $this->expiration_date,
            'is_active' => $this->is_active,
        ];
    }
}

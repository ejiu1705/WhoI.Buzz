<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SkpiCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'curriculum_version' => $this->curriculum_version,
            'code' => $this->code,
            'name_id' => $this->name_id,
            'name_en' => $this->name_en,
            'competency_domain' => $this->competency_domain,
            'is_mandatory' => $this->is_mandatory,
            'default_points' => $this->default_points,
            'level' => $this->level,
            'created_by' => $this->whenLoaded('creator', fn () => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
            ]),
            'activities_count' => $this->whenCounted('activities'),
        ];
    }
}

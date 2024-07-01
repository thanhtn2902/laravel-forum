<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body'  => $this->body,
            'user' => $this->whenLoaded('user', fn() => UserResource::make($this->user)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

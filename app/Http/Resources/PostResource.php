<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Number;
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
            'html'  => str($this->body)->markdown(),
            'user' =>  UserResource::make($this->whenLoaded('user')),
            'topic' =>  TopicResource::make($this->whenLoaded('topic')),
            'likes_count' => Number::abbreviate($this->likes_count),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'routes'    => [
                'show' => $this->showRoute(),
            ]
        ];
    }
}

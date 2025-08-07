<?php

namespace App\Http\Resources;

use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Number;

class PostResource extends JsonResource
{
    private bool $appendLikePermission = false;

    public function withLikePermission(): self
    {
        $this->appendLikePermission = true;

        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'body'        => $this->body,
            'html'        => str($this->body)->markdown(),
            'user'        => UserResource::make($this->whenLoaded('user')),
            'topic'       => TopicResource::make($this->whenLoaded('topic')),
            'likes_count' => Number::abbreviate($this->likes_count),
            'views_count' => Number::abbreviate($this->getViewsCount()),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'routes'      => [
                'show' => $this->showRoute(),
            ],
            'can' => [
                'like' => $this->when($this->appendLikePermission, fn () => $request->user()?->can('create', [Like::class, $this->resource])),
            ],
        ];
    }
}

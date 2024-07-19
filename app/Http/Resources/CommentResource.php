<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Number;

class CommentResource extends JsonResource
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
            'body'  => $this->body,
            'html'  => $this->html,
            'user' => UserResource::make($this->whenLoaded('user')),
            'post' => PostResource::make($this->whenLoaded('post')),
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::parse($this->updated_at)->format('Y-m-d H:i:s'),
            'likes_count' => Number::abbreviate($this->likes_count),
            'can'   => [
                'delete' => $request->user()?->can('delete', $this->resource),
                'update' => $request->user()?->can('update', $this->resource)
            ]
        ];
    }
}

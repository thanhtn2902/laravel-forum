<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'user' => $this->whenLoaded('user', fn () => UserResource::make($this->user)),
            'post' => $this->whenLoaded('post', fn () => PostResource::make($this->post)),
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::parse($this->updated_at)->format('Y-m-d H:i:s'),
            'can'   => [
                'delete' => $request->user()?->can('delete', $this->resource),
                'update' => $request->user()?->can('update', $this->resource)
            ]
        ];
    }
}

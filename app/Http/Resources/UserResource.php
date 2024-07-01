<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name'  => $this->name,
            'email' => $this->when($this->id === $request->user?->id, $this->email),
            'profile_photo_url' => $this->profile_photo_path ?? 'https://ui-avatars.com/api/?name=' . $this->name . '&color=7F9CF5&background=EBF4FF&bold=true',
            'created_at'    => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at'    => Carbon::parse($this->updated_at)->format('Y-m-d H:i:s'),
        ];
    }
}

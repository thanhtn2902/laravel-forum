<?php

namespace App\Models;

use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ConvertMarkdownToHtml;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;
    use ConvertMarkdownToHtml;
    use Searchable;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function title(): Attribute
    {
        return Attribute::set(fn ($value) => Str::title($value));
    }

    public function body(): Attribute
    {
        return Attribute::set(fn ($value) => [
            'body' => $value,
            'html' => str($value)->markdown()
        ]);
    }

    public function showRoute(array $parameter = [])
    {
        return route('posts.show', [$this, Str::slug($this->title), ...$parameter]);
    }

    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }
}

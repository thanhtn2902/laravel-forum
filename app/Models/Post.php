<?php

namespace App\Models;

use App\Models\Concerns\ConvertMarkdownToHtml;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Post extends Model
{
    use ConvertMarkdownToHtml;
    use HasFactory;
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
            'html' => str($value)->markdown(),
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

    /**
     * Increment view count for this post in Redis cache
     * Only increment once per session to avoid inflated counts
     */
    public function incrementViews(): void
    {
        $sessionKey = "post_viewed:{$this->id}:".session()->getId();
        $viewKey = "post_views:{$this->id}:" . now()->format('Y-m-d');

        // Only increment if not already viewed in this session today
        if (!Redis::exists($sessionKey)) {
            Redis::incr($viewKey);

            // Mark as viewed in this session (expires at end of day)
            Redis::set($sessionKey, true, now()->endOfDay()->diffInSeconds());
        }
    }

    /**
     * Get view count for this post from Redis cache
     */
    public function getViewsCount(): int
    {
        $key = "post_views:{$this->id}:" . now()->format('Y-m-d');
        return (int) Redis::get($key) ?: 0;
    }
}

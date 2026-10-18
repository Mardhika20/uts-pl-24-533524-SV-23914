<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recipe extends Model
    {
        protected $table = 'recipes';

        protected $fillable = [
            'title',
            'description',
            'ingredients',
            'instructions',
            'cooking_time',
            'category_id',
        ];

        protected $casts = [
            'cooking_time' => 'integer',
            'category_id' => 'integer',
        ];

        public function category(): BelongsTo
        {
            return $this->belongsTo(Category::class);
        }
}

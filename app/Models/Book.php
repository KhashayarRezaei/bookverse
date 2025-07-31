<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'title',
        'author',
        'description',
        'price',
        'published_year',
        'isbn',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'published_year' => 'integer',
        ];
    }

    /**
     * Get the order items for this book.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}

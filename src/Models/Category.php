<?php

namespace Brazidev\Brazidesk\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'brazidesk_categories';

    protected $fillable = ['name', 'color'];

    /**
     * Indicates that this model should not be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get related tickets.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets()
    {
        return $this->hasMany('Brazidev\Brazidesk\Models\Ticket', 'category_id');
    }

    /**
     * Get related agents.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function agents()
    {
        return $this->belongsToMany('\Brazidev\Brazidesk\Models\Agent', 'brazidesk_categories_users', 'category_id', 'user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Page extends Model implements Auditable
{
    use HasFactory, HasTranslations, AuditableTrait;

    protected $fillable = [
        'title',
        'content', 'active', 'slug'
    ];

    public $translatable = ['title', 'content'];
}


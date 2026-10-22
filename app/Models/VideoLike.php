<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** « J'aime » sur une vidéo : un par visiteur (compte ou cookie, comme la galerie photo). */
class VideoLike extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['video_id', 'liker_key'];
}

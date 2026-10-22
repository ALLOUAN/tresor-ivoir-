<?php

namespace App\Services;

use App\Models\Video;
use App\Services\Ai\Catalog;
use App\Support\ImageUploadGuard;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Enregistrement d'une vidéo de la médiathèque (administration et espace prestataire) :
 * validation, lien YouTube/Vimeo ou fichier envoyé, image de couverture, fiches liées.
 */
class VideoSubmission
{
    /** Règles communes. $allowedLinks : clés de fiches autorisées (null = toutes celles du catalogue). */
    public function rules(bool $creating): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['required', Rule::in(array_keys(Video::CATEGORIES))],
            'source_type' => ['required', Rule::in(['link', 'upload'])],
            'external_url' => ['nullable', 'string', 'max:500'],
            'video_file' => ['nullable', 'file'],
            'poster_file' => ['nullable', 'file'],
            'orientation' => ['required', Rule::in(['portrait', 'landscape'])],
            'duration_sec' => ['nullable', 'integer', 'min:1', 'max:36000'],
            'city_id' => ['nullable', 'integer', 'exists:tourist_cities,id'],
            'links' => ['nullable', 'array', 'max:6'],
            'links.*' => ['string', 'max:40'],
        ];
    }

    /**
     * Applique la saisie à la vidéo (sans la sauvegarder).
     *
     * @param  list<string>|null  $allowedLinks
     */
    public function fill(Video $video, Request $request, ?array $allowedLinks = null): Video
    {
        $data = $request->validate($this->rules(! $video->exists));

        $video->fill([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'],
            'orientation' => $data['orientation'],
            'city_id' => $data['city_id'] ?? null,
        ]);
        if (! empty($data['duration_sec'])) {
            $video->duration_sec = (int) $data['duration_sec'];
        }

        if ($data['source_type'] === 'link') {
            $parsed = Video::parseExternal($data['external_url'] ?? null);
            if (! $parsed) {
                throw ValidationException::withMessages(['external_url' => __('Collez un lien YouTube ou Vimeo valide.')]);
            }
            if ($video->source === 'upload') {
                $this->delete($video->file_path);
            }
            [$video->source, $video->external_id] = $parsed;
            $video->file_path = null;
            $video->video_url = null;
        } else {
            $file = $this->usable($request->file('video_file'));
            if ($file) {
                $this->assertVideo($file);
                if ($video->source === 'upload') {
                    $this->delete($video->file_path);
                }
                $path = $this->store($file, 'videos', 'video');
                $video->source = 'upload';
                $video->file_path = $path;
                $video->video_url = '/storage/'.$path;
                $video->external_id = null;
            } elseif ($video->source !== 'upload' || ! $video->video_url) {
                throw ValidationException::withMessages(['video_file' => __('Choisissez un fichier vidéo (MP4, WebM ou MOV, 50 Mo max).')]);
            }
        }

        if ($poster = $this->usable($request->file('poster_file'))) {
            $ext = strtolower((string) $poster->getClientOriginalExtension());
            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true) || ($poster->getSize() ?? 0) > 5 * 1024 * 1024) {
                throw ValidationException::withMessages(['poster_file' => __('Couverture : JPG, PNG ou WebP, 5 Mo max.')]);
            }
            ImageUploadGuard::assert($poster, 'poster_file');
            $this->delete($video->poster_url ? substr($video->poster_url, strlen('/storage/')) : null);
            $video->poster_url = '/storage/'.$this->store($poster, 'videos/posters', 'cover');
        }

        $catalog = app(Catalog::class);
        $links = [];
        foreach ((array) ($data['links'] ?? []) as $key) {
            if ($catalog->get($key) && ($allowedLinks === null || in_array($key, $allowedLinks, true))) {
                $links[] = $key;
            }
        }
        $video->links = array_values(array_unique($links)) ?: null;

        return $video;
    }

    public function deleteFiles(Video $video): void
    {
        if ($video->source === 'upload') {
            $this->delete($video->file_path);
        }
        if ($video->poster_url && str_starts_with($video->poster_url, '/storage/videos/posters/')) {
            $this->delete(substr($video->poster_url, strlen('/storage/')));
        }
    }

    private function usable(?UploadedFile $file): ?UploadedFile
    {
        try {
            return $file instanceof UploadedFile && $file->isValid() && $file->getError() === \UPLOAD_ERR_OK ? $file : null;
        } catch (\ValueError) {
            return null;
        }
    }

    private function assertVideo(UploadedFile $file): void
    {
        $ext = strtolower((string) $file->getClientOriginalExtension());
        $mime = (string) $file->getMimeType();
        $size = (int) ($file->getSize() ?? 0);
        if (! in_array($ext, Video::UPLOAD_MIMES, true) || ! str_starts_with($mime, 'video/')) {
            throw ValidationException::withMessages(['video_file' => __('Format vidéo non accepté (MP4, WebM ou MOV).')]);
        }
        if ($size <= 0 || $size > Video::UPLOAD_MAX_KB * 1024) {
            throw ValidationException::withMessages(['video_file' => __('La vidéo dépasse 50 Mo. Pour une vidéo plus longue, publiez-la sur YouTube et collez le lien.')]);
        }
    }

    /** Stockage par flux (même méthode que les autres envois du site : évite realpath() sous Windows). */
    private function store(UploadedFile $file, string $folder, string $prefix): string
    {
        $handle = @fopen((string) $file->getPathname(), 'r');
        if (! is_resource($handle)) {
            throw ValidationException::withMessages(['video_file' => __('Le fichier envoyé est illisible. Réessayez.')]);
        }
        $path = $folder.'/'.$prefix.'_'.Str::random(40).'.'.(strtolower($file->getClientOriginalExtension()) ?: 'bin');
        try {
            Storage::disk('public')->put($path, $handle);
        } finally {
            is_resource($handle) && fclose($handle);
        }

        return $path;
    }

    private function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}

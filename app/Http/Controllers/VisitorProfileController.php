<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class VisitorProfileController extends Controller
{
    public function edit(): View
    {
        return view('visitor.profile', ['user' => Auth::user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $this->sanitizeEmptyUploads($request, ['avatar']);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'locale' => ['required', 'in:fr,en'],
            'current_password' => ['nullable', 'required_with:new_password'],
            'new_password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        $passwordChanged = false;
        if (! empty($data['new_password'])) {
            if (! Hash::check((string) $data['current_password'], (string) $user->password_hash)) {
                return back()->withErrors([
                    'current_password' => 'Mot de passe actuel incorrect.',
                ])->withInput();
            }

            $user->password_hash = (string) $data['new_password'];
            $passwordChanged = true;
        }

        $emailChanged = mb_strtolower((string) $data['email']) !== mb_strtolower((string) $user->email);
        $phoneChanged = ($data['phone'] ?: null) !== $user->phone;

        if ($this->hasUsableUploadedFile($request, 'avatar')) {
            $this->assertValidImage($request->file('avatar'), 'avatar', 3072);
            $this->deleteStoredFile($user->avatar_url);
            $user->avatar_url = $this->storeUploadedFile($request->file('avatar'), 'avatars', 'user-'.$user->id);
        }

        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'];
        $user->email = mb_strtolower((string) $data['email']);
        $user->phone = $data['phone'] ?: null;
        $user->locale = $data['locale'];
        $user->save();

        if ($passwordChanged) {
            \App\Services\AccountSecurityEventLogger::log($user, \App\Models\AccountSecurityEvent::TYPE_PASSWORD_CHANGED, $request);
        }
        if ($emailChanged) {
            \App\Services\AccountSecurityEventLogger::log($user, \App\Models\AccountSecurityEvent::TYPE_EMAIL_CHANGED, $request);
        }
        if ($phoneChanged) {
            \App\Services\AccountSecurityEventLogger::log($user, \App\Models\AccountSecurityEvent::TYPE_PHONE_CHANGED, $request);
        }

        return back()->with('success', 'Profil mis à jour avec succès.');
    }

    // ── HELPERS UPLOAD (mêmes garde-fous que LoginBackgroundImageController) ──

    private function sanitizeEmptyUploads(Request $request, array $fields): void
    {
        foreach ($fields as $field) {
            $file = $request->file($field);
            if (! $file instanceof UploadedFile) {
                continue;
            }
            $pathname = '';
            try {
                $pathname = (string) $file->getPathname();
            } catch (\ValueError) {
                $pathname = '';
            }
            if ($file->getError() === \UPLOAD_ERR_NO_FILE || $pathname === '') {
                $request->files->remove($field);
            }
        }
    }

    private function hasUsableUploadedFile(Request $request, string $field): bool
    {
        $file = $request->file($field);

        return $file instanceof UploadedFile && $this->isUsableUploadedFile($file);
    }

    private function isUsableUploadedFile(UploadedFile $file): bool
    {
        try {
            return $file->isValid() && $file->getError() === \UPLOAD_ERR_OK;
        } catch (\ValueError) {
            return false;
        }
    }

    private function assertValidImage(UploadedFile $file, string $field, int $maxKb): void
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        if (! in_array($extension, ['jpeg', 'jpg', 'png', 'webp'], true)) {
            abort(422, "Format image non autorisé pour {$field} (jpeg, jpg, png, webp).");
        }
        $size = (int) ($file->getSize() ?? 0);
        if ($size <= 0 || $size > ($maxKb * 1024)) {
            abort(422, "Le fichier {$field} dépasse la taille autorisée.");
        }
    }

    /**
     * Stockage via fopen() plutôt que UploadedFile::store() : sous Windows, store()
     * appelle realpath() en interne, qui peut échouer (ValueError) quand les noms
     * courts 8.3 sont désactivés sur le volume.
     */
    private function storeUploadedFile(UploadedFile $file, string $folder, string $prefix): string
    {
        $pathname = $file->getPathname();
        $handle = (is_string($pathname) && $pathname !== '') ? @fopen($pathname, 'r') : false;

        if (! is_resource($handle)) {
            abort(422, 'Le fichier téléversé est invalide. Veuillez le sélectionner à nouveau.');
        }

        $extension = strtolower($file->getClientOriginalExtension()) ?: 'bin';
        $relativePath = $folder.'/'.$prefix.'_'.Str::random(40).'.'.$extension;

        try {
            $stored = Storage::disk('public')->put($relativePath, $handle);
        } finally {
            is_resource($handle) && fclose($handle);
        }

        if (! $stored) {
            abort(422, 'Le fichier téléversé est invalide. Veuillez le sélectionner à nouveau.');
        }

        return '/storage/'.$relativePath;
    }

    private function deleteStoredFile(?string $url): void
    {
        if (! $url || ! str_starts_with($url, '/storage/')) {
            return;
        }
        $relative = ltrim(substr($url, strlen('/storage/')), '/');
        if ($relative !== '') {
            Storage::disk('public')->delete($relative);
        }
    }
}

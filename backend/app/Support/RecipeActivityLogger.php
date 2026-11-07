<?php

namespace App\Support;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RecipeActivityLogger
{
    public function handle(RequestHandled $event): void
    {
        $request = $event->request;
        $status = $event->response->getStatusCode();

        $isSaving = $request->isMethod('POST')
            && $request->is('api/recipes');

        $isReading = $request->isMethod('GET')
            && $request->is('api/recipes');

        if (! $isSaving && ! $isReading) {
            return;
        }

        // Identifikasi pelaku tanpa menyimpan data pribadi.
        $actor = 'guest';

        try {
            $user = $request->user();

            if ($user !== null) {
                $isAdmin = ($user->role ?? null) === 'admin'
                    || (bool) ($user->is_admin ?? false);

                $actor = $isAdmin
                    ? 'admin'
                    : 'authenticated_user';
            }
        } catch (Throwable) {
            $actor = 'unknown';
        }

        // INFO: permintaan menyimpan resep berhasil.
        if ($isSaving && $status >= 200 && $status < 300) {
            Log::info('recipe.save.success', [
                'actor' => $actor,
                'action' => 'save_recipe',
                'reason' => 'request_completed_successfully',
            ]);

            return;
        }

        // WARNING: API menolak data karena validasi gagal.
        if ($isSaving && $status === 422) {
            Log::warning('recipe.validation.failed', [
                'actor' => $actor,
                'action' => 'save_recipe',
                'reason' => 'validation_rules_not_met',
            ]);

            return;
        }

        // ERROR: permintaan gagal dan MySQL tidak dapat diakses.
        if ($status >= 500) {
            try {
                DB::select('SELECT 1');
            } catch (Throwable) {
                Log::error('recipe.database.connection_failed', [
                    'actor' => $actor,
                    'action' => $isSaving
                        ? 'save_recipe'
                        : 'load_recipe_list',
                    'reason' => 'database_unavailable',
                ]);
            }
        }
    }
}

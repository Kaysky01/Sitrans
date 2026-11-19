<?php

use App\Exceptions\AppError;
use App\Exceptions\ValidationError;
use App\Http\Middleware\AuthenticateApiKey;
use App\Http\Middleware\EnsureTenantIsActive;
use App\Http\Middleware\ResolveTenant;
use App\Support\Tenant\TenantNotResolvedException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant' => ResolveTenant::class,
            'tenant.active' => EnsureTenantIsActive::class,
            'api.key' => AuthenticateApiKey::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Semua request API atau XHR yang mengharapkan JSON dirender sebagai
        // JSON; request halaman web (termasuk Livewire) tetap pakai rendering
        // default Laravel + Livewire.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Format response error seragam untuk seluruh endpoint JSON:
        // { success: false, code: "STOK_TIDAK_CUKUP", message: "...", errors: {...} }
        // Detail teknis (stack trace) tetap hanya masuk ke log server.
        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            $code = 'KESALAHAN_SERVER';
            $message = 'Server sedang bermasalah, coba beberapa saat lagi.';
            $status = 500;
            $errors = [];

            if ($exception instanceof ValidationException) {
                $code = $exception instanceof ValidationError
                    ? $exception->errorCode
                    : 'VALIDASI_GAGAL';
                $message = 'Periksa kembali data yang kamu isi.';
                $status = 422;
                $errors = collect($exception->errors())
                    ->map(fn (array $messages): string => (string) reset($messages))
                    ->all();
            } elseif ($exception instanceof AppError) {
                $code = $exception->errorCode;
                $message = $exception->getMessage();
                $status = $exception->httpStatus;
                $errors = $exception->errors();
            } elseif ($exception instanceof AuthenticationException) {
                $code = 'SESI_BERAKHIR';
                $message = 'Sesi berakhir, silakan login kembali.';
                $status = 401;
            } elseif ($exception instanceof AuthorizationException) {
                $code = 'TANPA_IZIN';
                $message = 'Kamu tidak punya izin untuk aksi ini.';
                $status = 403;
            } elseif ($exception instanceof ModelNotFoundException) {
                $code = 'TIDAK_DITEMUKAN';
                $message = 'Data yang kamu cari tidak ditemukan. Mungkin sudah dihapus atau tidak pernah ada.';
                $status = 404;
            } elseif ($exception instanceof ThrottleRequestsException) {
                $code = 'TERLALU_BANYAK_PERCOBAAN';
                $message = 'Terlalu banyak percobaan. Tunggu beberapa menit lalu coba lagi.';
                $status = 429;
            } elseif ($exception instanceof TenantNotResolvedException) {
                $code = 'KAFE_TIDAK_TERHUBUNG';
                $message = 'Kafe tidak dikenali dari request ini. Periksa API key atau login kembali.';
                $status = 403;
            } elseif ($exception instanceof HttpExceptionInterface) {
                $status = $exception->getStatusCode();

                // Pesan abort() yang memang ditulis ramah (bahasa Indonesia)
                // dipertahankan; pesan bawaan Symfony diganti dengan pesan
                // generik yang ramah pengguna.
                $custom = $exception->getMessage();
                $generic = [
                    400 => 'Permintaan tidak valid.',
                    401 => 'Sesi berakhir, silakan login kembali.',
                    403 => 'Kamu tidak punya izin untuk aksi ini.',
                    404 => 'Data yang kamu cari tidak ditemukan.',
                    409 => 'Perubahan tidak bisa diproses karena data sudah berubah.',
                    422 => 'Periksa kembali data yang kamu isi.',
                    429 => 'Terlalu banyak percobaan. Tunggu beberapa menit lalu coba lagi.',
                ];
                $code = match (true) {
                    $status === 401 => 'SESI_BERAKHIR',
                    $status === 403 => 'TANPA_IZIN',
                    $status === 404 => 'TIDAK_DITEMUKAN',
                    $status === 409 => 'KONFLIK_STATUS',
                    $status === 422 => 'VALIDASI_GAGAL',
                    $status === 429 => 'TERLALU_BANYAK_PERCOBAAN',
                    $status >= 500 => 'KESALAHAN_SERVER',
                    default => 'PERMINTAAN_TIDAK_VALID',
                };
                $message = $generic[$status] ?? (mb_strpos((string) $custom, ' ') !== false ? $custom : $message);

                if ($status >= 500) {
                    $message = 'Server sedang bermasalah, coba beberapa saat lagi.';
                }
            }

            return response()->json([
                'success' => false,
                'code' => $code,
                'message' => $message,
                'errors' => $errors,
            ], $status);
        });

        // Konteks teknis untuk log server (bukan untuk pengguna).
        $exceptions->context(function (Throwable $exception, array $context): array {
            return [
                ...$context,
                'endpoint' => request()->getMethod().' '.request()->getPathInfo(),
                'user_id' => auth()->id(),
            ];
        });
    })->create();

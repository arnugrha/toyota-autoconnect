<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditTrailService
{
    /**
     * Keys that must never be recorded in raw format in audit logs.
     */
    protected const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'remember_token',
        'server_key',
        'client_secret',
        'secret',
        'token',
        'snap_token',
        'api_key',
        'cvv',
        'card_number',
        'credit_card',
    ];

    /**
     * Create an immutable audit log entry.
     */
    public function log(
        string $action,
        string $module,
        ?string $description = null,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?User $actor = null
    ): AuditLog {
        $user = $actor ?? Auth::user();

        $userName = $user ? $user->name : 'Sistem / Tamu';
        $userRole = null;
        if ($user) {
            $userRole = method_exists($user, 'getRoleNames') && $user->getRoleNames()->isNotEmpty()
                ? $user->getRoleNames()->first()
                : 'User';
        }

        // Sanitize & filter values
        $sanitizedOld = $oldValues !== null ? $this->sanitizeValues($oldValues) : null;
        $sanitizedNew = $newValues !== null ? $this->sanitizeValues($newValues) : null;

        return AuditLog::create([
            'user_id' => $user?->id,
            'user_name' => $userName,
            'user_role' => $userRole,
            'action' => $action,
            'module' => $module,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable?->getKey(),
            'description' => $description,
            'old_values' => $sanitizedOld,
            'new_values' => $sanitizedNew,
            'ip_address' => Request::ip() ?? '127.0.0.1',
            'user_agent' => Request::userAgent() ?? 'CLI / Internal',
            'created_at' => now(),
        ]);
    }

    /**
     * Log authentication events (login, login_failed, logout, password_change).
     */
    public function logAuth(string $action, User|string $user, bool $success = true, ?string $reason = null): AuditLog
    {
        $userId = $user instanceof User ? $user->id : null;
        $userName = $user instanceof User ? $user->name : $user;
        $userRole = $user instanceof User && method_exists($user, 'getRoleNames')
            ? $user->getRoleNames()->first()
            : null;

        $desc = match ($action) {
            'login' => $success ? "Pengguna {$userName} berhasil masuk ke sistem." : "Percobaan login gagal untuk {$userName}. Alasan: {$reason}",
            'logout' => "Pengguna {$userName} keluar dari sistem.",
            'password_change' => "Pengguna {$userName} berhasil memperbarui kata sandi akun.",
            default => "Aktivitas autentikasi: {$action}",
        };

        return AuditLog::create([
            'user_id' => $userId,
            'user_name' => $userName,
            'user_role' => $userRole,
            'action' => $action,
            'module' => 'auth',
            'description' => $desc,
            'old_values' => null,
            'new_values' => $success ? ['status' => 'success'] : ['status' => 'failed', 'reason' => $reason],
            'ip_address' => Request::ip() ?? '127.0.0.1',
            'user_agent' => Request::userAgent() ?? 'CLI / Internal',
            'created_at' => now(),
        ]);
    }

    /**
     * Log changes to system settings.
     */
    public function logSettings(string $group, array $oldValues, array $newValues, ?User $actor = null): AuditLog
    {
        $desc = "Pembaruan pengaturan grup [{$group}] oleh " . ($actor ? $actor->name : 'Administrator');

        return $this->log(
            action: 'settings_updated',
            module: 'system_settings',
            description: $desc,
            oldValues: $oldValues,
            newValues: $newValues,
            actor: $actor
        );
    }

    /**
     * Log model creation.
     */
    public function logCreated(Model $model, string $module, ?string $description = null, ?User $actor = null): AuditLog
    {
        $desc = $description ?? "Pembuatan data baru pada modul {$module} ID #{$model->getKey()}";

        return $this->log(
            action: 'created',
            module: $module,
            description: $desc,
            auditable: $model,
            newValues: $model->getAttributes(),
            actor: $actor
        );
    }

    /**
     * Log model update with diff between dirty fields.
     */
    public function logUpdated(Model $model, string $module, array $oldValues, array $newValues, ?string $description = null, ?User $actor = null): ?AuditLog
    {
        // Compute difference
        $diffOld = [];
        $diffNew = [];

        foreach ($newValues as $key => $newVal) {
            $oldVal = $oldValues[$key] ?? null;
            if ($oldVal != $newVal) {
                $diffOld[$key] = $oldVal;
                $diffNew[$key] = $newVal;
            }
        }

        if (empty($diffNew)) {
            return null; // No meaningful changes
        }

        $desc = $description ?? "Pembaruan data pada modul {$module} ID #{$model->getKey()}";

        return $this->log(
            action: 'updated',
            module: $module,
            description: $desc,
            auditable: $model,
            oldValues: $diffOld,
            newValues: $diffNew,
            actor: $actor
        );
    }

    /**
     * Log model deletion.
     */
    public function logDeleted(Model $model, string $module, ?string $description = null, ?User $actor = null): AuditLog
    {
        $desc = $description ?? "Penghapusan data pada modul {$module} ID #{$model->getKey()}";

        return $this->log(
            action: 'deleted',
            module: $module,
            description: $desc,
            auditable: $model,
            oldValues: $model->getAttributes(),
            actor: $actor
        );
    }

    /**
     * Log business status transition.
     */
    public function logStatusChanged(Model $model, string $module, string $oldStatus, string $newStatus, ?string $reason = null, ?User $actor = null): AuditLog
    {
        $desc = "Perubahan status {$module} ID #{$model->getKey()} dari '{$oldStatus}' menjadi '{$newStatus}'." . ($reason ? " Alasan: {$reason}" : '');

        return $this->log(
            action: 'status_changed',
            module: $module,
            description: $desc,
            auditable: $model,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $newStatus, 'reason' => $reason],
            actor: $actor
        );
    }

    /**
     * Recursively mask or remove sensitive attributes.
     */
    protected function sanitizeValues(array $values): array
    {
        $clean = [];

        foreach ($values as $key => $val) {
            $lowerKey = strtolower((string) $key);

            $isSensitive = false;
            foreach (self::SENSITIVE_KEYS as $sensitive) {
                if (str_contains($lowerKey, $sensitive)) {
                    $isSensitive = true;
                    break;
                }
            }

            if ($isSensitive) {
                $clean[$key] = '[PROTECTED]';
            } elseif (is_array($val)) {
                $clean[$key] = $this->sanitizeValues($val);
            } else {
                $clean[$key] = $val;
            }
        }

        return $clean;
    }
}


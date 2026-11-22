<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\HubNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    public function log(string $action, ?Model $subject = null, array $changes = [], ?User $actor = null): AuditLog
    {
        $user = $actor ?? Auth::user();
        $request = request();

        return AuditLog::create([
            'actor_id' => $user?->id,
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'ip_address' => $request?->ip(),
            'user_agent' => substr((string) ($request?->userAgent() ?? 'cli'), 0, 255),
            'changes_json' => $changes,
            'created_at' => now(),
        ]);
    }

    public function notify(User $recipient, string $type, string $title, string $message, ?string $actionUrl = null): HubNotification
    {
        return HubNotification::create([
            'user_id' => $recipient->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminAuditLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        return Inertia::render('Admin/AuditLogs', [
            'logs' => AuditLog::query()
                ->with('user:id,name,email')
                ->when($filters['q'] ?? null, fn ($query, $term) => $query
                    ->where('action', 'like', "%{$term}%")
                    ->orWhere('entity_type', 'like', "%{$term}%"))
                ->latest('created_at')
                ->paginate(20)
                ->withQueryString()
                ->through(fn (AuditLog $log) => [
                    'id' => $log->id,
                    'action' => $log->action,
                    'entity_type' => $log->entity_type,
                    'entity_id' => $log->entity_id,
                    'actor' => $log->user ? [
                        'name' => $log->user->name,
                        'email' => $log->user->email,
                    ] : null,
                    'ip_address' => $log->ip_address,
                    'created_at' => $log->created_at?->toDateTimeString(),
                    'new_data' => $log->new_data,
                ]),
            'filters' => ['q' => $filters['q'] ?? ''],
        ]);
    }
}

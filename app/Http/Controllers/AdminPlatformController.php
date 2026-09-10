<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class AdminPlatformController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Platform', [
            'integrations' => [
                [
                    'name' => 'Recurring payments',
                    'provider' => config('services.payments.provider', 'stripe'),
                    'status' => config('services.stripe.secret') ? 'configured' : 'demo_ready',
                    'notes' => 'Gateway boundary exists; current demo uses simulated paid transactions until live credentials are added.',
                ],
                [
                    'name' => 'Secure video streaming',
                    'provider' => config('services.video.provider', 'mux'),
                    'status' => config('services.mux.secret') ? 'configured' : 'demo_ready',
                    'notes' => 'Lessons store provider/video IDs and thumbnails; signed playback can be attached when streaming keys are available.',
                ],
                [
                    'name' => 'Email/SMS/push notifications',
                    'provider' => config('mail.default').' / in-app',
                    'status' => 'in_app_ready',
                    'notes' => 'In-app notification center is live; mail/SMS/push channels need provider credentials.',
                ],
                [
                    'name' => 'Backups and HTTPS',
                    'provider' => 'deployment',
                    'status' => app()->environment('production') ? 'production' : 'deployment_needed',
                    'notes' => 'Application is prepared for production hosting; backup schedule and TLS are server/deployment tasks.',
                ],
                [
                    'name' => 'Security controls',
                    'provider' => 'Laravel auth + RBAC',
                    'status' => 'ready',
                    'notes' => 'Has hashed passwords, verified users, active account checks, role permissions and audit logs.',
                ],
            ],
        ]);
    }
}

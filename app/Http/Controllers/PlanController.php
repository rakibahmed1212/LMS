<?php

namespace App\Http\Controllers;

use App\Models\ClassYear;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $plans = ClassYear::query()
            ->where('is_active', true)
            ->with(['subjects' => fn ($q) => $q->where('is_active', true)->with([
                'plans' => fn ($q) => $q->where('is_active', true),
                'courses' => fn ($q) => $q->where('is_published', true),
            ])->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($year) => [
                'id' => $year->id,
                'name' => $year->name,
                'subjects' => $year->subjects->map(fn ($subject) => [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'color' => $subject->color,
                    'plans' => $subject->plans->map(fn ($plan) => [
                        'id' => $plan->id,
                        'name' => $plan->name,
                        'billing_cycle' => $plan->billing_cycle,
                        'price' => (float) $plan->price,
                        'trial_days' => $plan->trial_days,
                        'is_bundle' => $plan->is_bundle,
                    ]),
                    'has_course' => $subject->courses->isNotEmpty(),
                ]),
            ]);

        return Inertia::render('Plans/Index', [
            'years' => $plans,
        ]);
    }
}

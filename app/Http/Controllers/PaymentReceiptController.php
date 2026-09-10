<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentReceiptController extends Controller
{
    public function show(Request $request, Payment $payment)
    {
        $payment->load('subscription.student.parent', 'subscription.plan.subjects.classYear');
        $student = $payment->subscription->student;

        abort_unless(
            $student->parent_id === $request->user()->id || $request->user()->can('view payments'),
            403,
        );

        return Inertia::render('Payments/Receipt', [
            'receipt' => [
                'invoice_no' => $payment->invoice_no,
                'status' => $payment->status,
                'gateway' => $payment->gateway,
                'transaction_id' => $payment->gateway_transaction_id,
                'amount' => (float) $payment->amount,
                'discount_amount' => (float) $payment->discount_amount,
                'total' => (float) $payment->total,
                'currency' => $payment->currency,
                'paid_at' => $payment->paid_at?->toDateTimeString(),
                'parent' => [
                    'name' => $student->parent?->name,
                    'email' => $student->parent?->email,
                    'phone' => $student->parent?->phone,
                ],
                'student' => [
                    'name' => $student->name,
                    'student_code' => $student->student_code,
                ],
                'subscription' => [
                    'plan' => $payment->subscription->plan?->name,
                    'status' => $payment->subscription->status,
                    'billing_cycle' => $payment->subscription->billing_cycle,
                    'expires_at' => $payment->subscription->expires_at?->toDateString(),
                    'subjects' => $payment->subscription->plan?->subjects
                        ->map(fn ($subject) => trim(($subject->classYear?->name ? $subject->classYear->name.' ' : '').$subject->name))
                        ->values() ?? [],
                ],
            ],
        ]);
    }
}

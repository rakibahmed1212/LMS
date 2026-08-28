<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A plan targets one or more subjects (pivot enables bundle e.g. Maths + English).
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('billing_cycle', ['monthly', 'annual'])->default('monthly');
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('GBP');
            $table->string('title_internal')->nullable(); // e.g. "Year 3 Maths + English Bundle"
            $table->unsignedInteger('trial_days')->nullable(); // overrides global trial if set
            $table->boolean('is_bundle')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Bundle pivot: single-subject plans have exactly one row here.
        Schema::create('subscription_plan_subject', function (Blueprint $table) {
            $table->foreignId('subscription_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->primary(['subscription_plan_id', 'subject_id']);
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['percent', 'fixed'])->default('percent');
            $table->decimal('value', 10, 2);
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', [
                'pending', 'trial', 'active', 'past_due', 'paused',
                'cancelled', 'expired', 'blocked',
            ])->default('pending');
            $table->timestamp('started_at');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('grace_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->enum('billing_cycle', ['monthly', 'annual'])->default('monthly');
            $table->decimal('subscribed_price', 10, 2);
            $table->string('currency', 3)->default('GBP');
            $table->string('dunning_stage', 20)->default('none'); // none|dunning_1|dunning_2|dunning_3|cancelled
            $table->unsignedSmallInteger('dunning_attempts')->default(0);
            $table->string('gateway', 30)->nullable();            // stripe|sslcommerz|bKash|manual
            $table->string('gateway_subscription_id')->nullable(); // provider token (never raw card data)
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'status']);
            $table->index(['expires_at']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_no', 40)->unique();
            $table->decimal('amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('currency', 3)->default('GBP');
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('gateway', 30)->nullable();
            $table->string('gateway_transaction_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->index(['status', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('subscription_plan_subject');
        Schema::dropIfExists('subscription_plans');
    }
};

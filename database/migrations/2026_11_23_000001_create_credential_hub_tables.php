<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nim', 32)->unique();
            $table->string('prodi_name');
            $table->string('faculty_name');
            $table->unsignedTinyInteger('semester')->default(1);
            $table->foreignUlid('advisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('public_slug', 64)->unique();
            $table->boolean('is_public_enabled')->default(false)->index(); // Privacy-by-Default (UU PDP)
            $table->text('phone')->nullable(); // Encrypted at rest via Laravel cast
            $table->string('headline')->nullable();
            $table->text('bio')->nullable();
            $table->json('skills_json')->nullable();
            $table->json('embedding')->nullable(); // Semantic vector (compatible with pgvector & SQLite)
            $table->string('yudisium_status', 32)->default('active'); // active, eligible, graduated
            $table->timestamps();
        });

        Schema::create('skpi_categories', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('curriculum_version', 32)->default('2026-V1');
            $table->string('code', 32)->unique();
            $table->string('name_id');
            $table->string('name_en');
            $table->string('competency_domain', 32)->index(); // Leadership, Technical, Communication, Social, Academic
            $table->boolean('is_mandatory')->default(false);
            $table->unsignedInteger('default_points')->default(15);
            $table->string('level', 32)->default('Nasional'); // Lokal, Nasional, Internasional
            $table->timestamps();
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('category_id')->constrained('skpi_categories');
            $table->string('title_id');
            $table->string('title_en');
            $table->string('organizer');
            $table->string('role_in_activity')->default('Peserta');
            $table->date('activity_date');
            $table->string('status', 24)->default('draft')->index(); // draft, submitted, revision, approved, revoked
            $table->unsignedTinyInteger('trust_tier')->default(3)->index(); // 1=Campus Issued, 2=Verified External, 3=Admin Reviewed
            $table->unsignedInteger('awarded_points')->default(0);
            $table->string('external_verify_url', 512)->nullable();
            $table->boolean('is_public')->default(true);
            $table->unsignedTinyInteger('ai_confidence_score')->nullable();
            $table->json('ai_extracted_payload')->nullable();
            $table->timestamp('locked_at')->nullable(); // Immutable once approved
            $table->timestamps();
        });

        Schema::create('activity_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUlid('activity_id')->constrained('activities')->cascadeOnDelete();
            $table->foreignUlid('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('disk', 32)->default('private_vault');
            $table->string('storage_path');
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->string('sha256_hash', 64);
            $table->timestamps();

            $table->unique(['student_id', 'sha256_hash'], 'activity_files_student_sha256_unique');
        });

        Schema::create('activity_validations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('activity_id')->constrained('activities')->cascadeOnDelete();
            $table->foreignUlid('reviewer_id')->constrained('users');
            $table->string('decision', 24); // approved, revision, revoked
            $table->text('notes')->nullable();
            $table->timestamp('reviewed_at');
            $table->timestamps();
        });

        Schema::create('issued_credentials', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('activity_id')->nullable()->constrained('activities')->nullOnDelete();
            $table->string('type', 24)->index(); // skpi, certificate
            $table->string('title');
            $table->string('registration_number', 64)->unique();
            $table->json('payload_json');
            $table->string('signature_hash', 256);
            $table->string('signature_algo', 32)->default('Ed25519+HMAC-SHA256');
            $table->timestamp('issued_at');
            $table->timestamp('revoked_at')->nullable();
            $table->text('revoke_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 64)->index();
            $table->string('subject_type', 128)->nullable();
            $table->string('subject_id', 64)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->json('changes_json')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('hub_notifications', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 48); // skpi_ready, activity_approved, activity_revision, auto_claim
            $table->string('title');
            $table->text('message');
            $table->string('action_url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hub_notifications');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('issued_credentials');
        Schema::dropIfExists('activity_validations');
        Schema::dropIfExists('activity_files');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('skpi_categories');
        Schema::dropIfExists('student_profiles');
    }
};

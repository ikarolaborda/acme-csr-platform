<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('short_description')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('category', [
                'environment',
                'education',
                'health',
                'community',
                'disaster_relief',
                'poverty',
                'other'
            ]);
            $table->decimal('goal_amount', 12, 2);
            $table->decimal('current_amount', 12, 2)->default(0);
            $table->integer('donors_count')->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'pending', 'active', 'completed', 'cancelled'])->default('draft');
            $table->string('featured_image')->nullable();
            $table->json('images')->nullable();
            $table->json('documents')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('views_count')->default(0);
            $table->text('impact_description')->nullable();
            $table->json('milestones')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'start_date', 'end_date']);
            $table->index('category');
            $table->index('user_id');
            
            // Only add fulltext index for MySQL/MariaDB
            if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
                $table->fullText(['title', 'description']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};

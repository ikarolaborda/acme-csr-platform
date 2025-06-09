<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_id')->unique()->after('id');
            $table->string('department')->nullable()->after('email');
            $table->string('position')->nullable()->after('department');
            $table->enum('role', ['employee', 'admin', 'super_admin'])->default('employee')->after('position');
            $table->boolean('is_active')->default(true)->after('role');
            $table->string('phone')->nullable()->after('is_active');
            $table->text('bio')->nullable()->after('phone');
            $table->string('avatar')->nullable()->after('bio');
            $table->decimal('total_donated', 12, 2)->default(0)->after('avatar');
            $table->integer('campaigns_created')->default(0)->after('total_donated');
            $table->timestamp('last_login_at')->nullable()->after('campaigns_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'employee_id',
                'department',
                'position',
                'role',
                'is_active',
                'phone',
                'bio',
                'avatar',
                'total_donated',
                'campaigns_created',
                'last_login_at'
            ]);
        });
    }
};

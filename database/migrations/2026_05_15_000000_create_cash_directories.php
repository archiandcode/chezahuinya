<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('currency', 3)->default('KZT');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->date('opening_balance_date')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('cash_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('short_name')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('cash_flow_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('direction')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->foreignId('cash_register_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('cash_company_id')->nullable()->after('company')->constrained('cash_companies')->nullOnDelete();
            $table->foreignId('cash_flow_category_id')->nullable()->after('cash_flow')->constrained('cash_flow_categories')->nullOnDelete();
        });

        $registerId = DB::table('cash_registers')->insertGetId([
            'name' => 'Основная касса',
            'currency' => 'KZT',
            'opening_balance' => 318943.20,
            'opening_balance_date' => '2026-01-05',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('cash_transactions')->update([
            'cash_register_id' => $registerId,
        ]);

        DB::table('cash_transactions')
            ->whereNotNull('company')
            ->where('company', '<>', '')
            ->distinct()
            ->orderBy('company')
            ->pluck('company')
            ->each(function (string $name): void {
                DB::table('cash_companies')->insertOrIgnore([
                    'name' => $name,
                    'short_name' => null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        DB::table('cash_companies')
            ->get(['id', 'name'])
            ->each(function (object $company): void {
                DB::table('cash_transactions')
                    ->where('company', $company->name)
                    ->update(['cash_company_id' => $company->id]);
            });

        DB::table('cash_transactions')
            ->whereNotNull('cash_flow')
            ->where('cash_flow', '<>', '')
            ->distinct()
            ->orderBy('cash_flow')
            ->pluck('cash_flow')
            ->each(function (string $name): void {
                DB::table('cash_flow_categories')->insertOrIgnore([
                    'name' => $name,
                    'direction' => null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        DB::table('cash_flow_categories')
            ->get(['id', 'name'])
            ->each(function (object $category): void {
                DB::table('cash_transactions')
                    ->where('cash_flow', $category->name)
                    ->update(['cash_flow_category_id' => $category->id]);
            });
    }

    public function down(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cash_register_id');
            $table->dropConstrainedForeignId('cash_company_id');
            $table->dropConstrainedForeignId('cash_flow_category_id');
        });

        Schema::dropIfExists('cash_flow_categories');
        Schema::dropIfExists('cash_companies');
        Schema::dropIfExists('cash_registers');
    }
};

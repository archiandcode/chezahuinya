<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('construction_sections', 'route_name')) {
            Schema::table('construction_sections', function (Blueprint $table) {
                $table->dropColumn('route_name');
            });
        }

        if (! Schema::hasColumn('construction_payments', 'construction_section_id')) {
            Schema::table('construction_payments', function (Blueprint $table) {
                $table->foreignId('construction_section_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('construction_sections')
                    ->nullOnDelete();
            });
        }

        $defaultSectionId = DB::table('construction_sections')
            ->where('name', 'Реализация')
            ->value('id');

        if ($defaultSectionId) {
            DB::table('construction_payments')
                ->whereNull('construction_section_id')
                ->update(['construction_section_id' => $defaultSectionId]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('construction_payments', 'construction_section_id')) {
            Schema::table('construction_payments', function (Blueprint $table) {
                $table->dropConstrainedForeignId('construction_section_id');
            });
        }

        if (! Schema::hasColumn('construction_sections', 'route_name')) {
            Schema::table('construction_sections', function (Blueprint $table) {
                $table->string('route_name')->nullable()->after('name');
            });
        }
    }
};

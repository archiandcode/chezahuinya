<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('construction_sections', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        collect([
            ['name' => 'Реализация', 'sort_order' => 10],
            ['name' => 'Журналы', 'sort_order' => 20],
            ['name' => 'Прочие р-ды по ним', 'sort_order' => 30],
            ['name' => 'Отчет ОДДС стройка', 'sort_order' => 40],
        ])->each(function (array $section): void {
            DB::table('construction_sections')->insert([
                ...$section,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('construction_sections');
    }
};

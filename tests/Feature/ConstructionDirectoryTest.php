<?php

namespace Tests\Feature;

use App\Models\ConstructionSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Tests\TestCase;

#[RequiresPhpExtension('pdo_pgsql')]
class ConstructionDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_construction_directories(): void
    {
        $this->get(route('construction-directories.index'))
            ->assertRedirect(route('login'));
    }

    public function test_construction_menu_uses_active_sections_from_directory(): void
    {
        $user = User::factory()->create();
        ConstructionSection::query()->delete();

        ConstructionSection::factory()->create([
            'name' => 'Реализация тест',
            'sort_order' => 10,
        ]);
        ConstructionSection::factory()->create([
            'name' => 'Журналы тест',
            'sort_order' => 20,
        ]);
        ConstructionSection::factory()->inactive()->create([
            'name' => 'Скрытый раздел',
        ]);

        $this->actingAs($user)
            ->get(route('construction-payments.index'))
            ->assertOk()
            ->assertSee('Стройка')
            ->assertSee('Реализация тест')
            ->assertSee('Журналы тест')
            ->assertSee('construction_section_id', false)
            ->assertSee('Справочники')
            ->assertDontSee('Скрытый раздел');
    }

    public function test_construction_section_can_be_created_updated_and_deleted(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('construction-sections.store'), [
                'name' => 'Новый раздел',
                'sort_order' => '50',
                'is_active' => '1',
            ])
            ->assertRedirect(route('construction-directories.index'));

        $section = ConstructionSection::query()->where('name', 'Новый раздел')->firstOrFail();

        $this->assertDatabaseHas('construction_sections', [
            'id' => $section->id,
            'name' => 'Новый раздел',
            'sort_order' => 50,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->put(route('construction-sections.update', $section), [
                'name' => 'Обновленный раздел',
                'sort_order' => '60',
            ])
            ->assertRedirect(route('construction-directories.index'));

        $this->assertDatabaseHas('construction_sections', [
            'id' => $section->id,
            'name' => 'Обновленный раздел',
            'sort_order' => 60,
            'is_active' => false,
        ]);

        $this->actingAs($user)
            ->delete(route('construction-sections.destroy', $section))
            ->assertRedirect(route('construction-directories.index'));

        $this->assertDatabaseMissing('construction_sections', [
            'id' => $section->id,
        ]);
    }

    public function test_construction_directories_page_uses_cash_directory_style_controls(): void
    {
        $user = User::factory()->create();
        $section = ConstructionSection::factory()->create([
            'name' => 'Реализация стиль',
        ]);

        $this->actingAs($user)
            ->get(route('construction-directories.index'))
            ->assertOk()
            ->assertSee('directory-tabs')
            ->assertSee('directory-toolbar')
            ->assertSee('Новый раздел')
            ->assertSee('data-target="#createConstructionSectionModal"', false)
            ->assertSee('data-target="#editConstructionSectionModal"', false)
            ->assertSee($section->name);
    }

    public function test_construction_section_validation_rejects_duplicate_names(): void
    {
        $user = User::factory()->create();
        $name = 'Раздел '.str()->random(8);

        ConstructionSection::factory()->create([
            'name' => $name,
        ]);

        $this->actingAs($user)
            ->post(route('construction-sections.store'), [
                'name' => $name,
            ])
            ->assertSessionHasErrors(['name']);
    }
}

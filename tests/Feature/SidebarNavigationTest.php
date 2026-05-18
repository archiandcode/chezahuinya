<?php

namespace Tests\Feature;

use App\Models\CashRegister;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Tests\TestCase;

#[RequiresPhpExtension('pdo_pgsql')]
class SidebarNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_temporarily_shows_only_cash_section(): void
    {
        $user = User::factory()->create();

        CashRegister::factory()->create(['name' => 'Касса ИП']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Касса')
            ->assertSee('Касса ИП')
            ->assertSee('Отчет ОДДС')
            ->assertSee('Справочники')
            ->assertDontSee('Стройка')
            ->assertDontSee('Реализация')
            ->assertDontSee('Ежедневные отчеты')
            ->assertDontSee('Еженедельные отчеты')
            ->assertDontSee('Прочие')
            ->assertDontSee('Май 2025')
            ->assertDontSee('19 мая')
            ->assertDontSee('20 мая')
            ->assertDontSee('21 мая');
    }
}

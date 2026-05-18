<?php

namespace Tests\Feature;

use App\Models\ConstructionPayment;
use App\Models\ConstructionSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Tests\TestCase;

#[RequiresPhpExtension('pdo_pgsql')]
class ConstructionPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_construction_payments(): void
    {
        $this->get(route('construction-payments.index'))
            ->assertRedirect(route('login'));
    }

    public function test_selected_construction_section_is_applied_as_context_and_hidden_from_filter_block(): void
    {
        $user = User::factory()->create();
        $selectedSection = ConstructionSection::factory()->create(['name' => 'Реализация '.str()->random(8)]);
        $otherSection = ConstructionSection::factory()->create(['name' => 'Журналы '.str()->random(8)]);

        ConstructionPayment::factory()->forSection($selectedSection)->create([
            'payment_date' => '2026-05-18',
            'supplier' => 'Поставщик выбранного раздела',
            'amount' => 1000,
        ]);
        ConstructionPayment::factory()->forSection($otherSection)->create([
            'payment_date' => '2026-05-18',
            'supplier' => 'Поставщик другого раздела',
            'amount' => 2000,
        ]);

        $this->actingAs($user)
            ->get(route('construction-payments.index', ['construction_section_id' => $selectedSection->id]))
            ->assertOk()
            ->assertSee('<h1 class="m-0">'.$selectedSection->name.'</h1>', false)
            ->assertSee('1 000.00')
            ->assertSee('<td>Поставщик выбранного раздела</td>', false)
            ->assertDontSee('<td class="text-right font-weight-bold">2 000.00</td>', false)
            ->assertDontSee('<td>Поставщик другого раздела</td>', false)
            ->assertDontSee('for="construction_section_id"', false)
            ->assertSee('name="construction_section_id" value="'.$selectedSection->id.'"', false);
    }

    public function test_construction_payment_filters_apply_inside_selected_section(): void
    {
        $user = User::factory()->create();
        $section = ConstructionSection::factory()->create();

        ConstructionPayment::factory()->forSection($section)->create([
            'payment_date' => '2026-05-18',
            'supplier' => 'ТОО Строй Альфа',
            'contract' => 'Договор А',
            'payment_source' => 'Касса 1',
            'amount' => 1500,
        ]);
        ConstructionPayment::factory()->forSection($section)->create([
            'payment_date' => '2026-05-19',
            'supplier' => 'ТОО Строй Бета',
            'contract' => 'Договор Б',
            'payment_source' => 'Касса 2',
            'amount' => 700,
        ]);

        $this->actingAs($user)
            ->get(route('construction-payments.index', [
                'construction_section_id' => $section->id,
                'date_from' => '2026-05-18',
                'date_to' => '2026-05-18',
                'supplier' => 'ТОО Строй Альфа',
                'contract' => 'Договор А',
                'payment_source' => 'Касса 1',
                'amount_from' => '1000',
                'amount_to' => '2000',
            ]))
            ->assertOk()
            ->assertSee('1 500.00')
            ->assertSee('ТОО Строй Альфа')
            ->assertDontSee('700.00')
            ->assertSee('Дата от:')
            ->assertSee('Поставщик:')
            ->assertSee('Договор:')
            ->assertSee('Касса / р/с:')
            ->assertSee('Сумма от:')
            ->assertSee('Сумма до:');
    }

    public function test_construction_payment_can_be_created_and_redirects_back_to_current_filters(): void
    {
        $user = User::factory()->create();
        $section = ConstructionSection::factory()->create();

        $this->actingAs($user)
            ->post(route('construction-payments.store'), [
                'construction_section_id' => $section->id,
                'payment_date' => '2026-05-18',
                'supplier' => 'ТОО Монтаж',
                'amount' => '3000.25',
                'contract' => 'Договор 15',
                'purpose' => 'Оплата работ',
                'payment_source' => 'Расчетный счет',
                'filter_construction_section_id' => $section->id,
                'filter_date_from' => '2026-05-01',
                'filter_per_page' => '25',
            ])
            ->assertRedirect(route('construction-payments.index', [
                'construction_section_id' => $section->id,
                'date_from' => '2026-05-01',
                'per_page' => '25',
            ]));

        $this->assertDatabaseHas('construction_payments', [
            'construction_section_id' => $section->id,
            'payment_date' => '2026-05-18',
            'supplier' => 'ТОО Монтаж',
            'amount' => '3000.25',
            'contract' => 'Договор 15',
            'purpose' => 'Оплата работ',
            'payment_source' => 'Расчетный счет',
        ]);
    }

    public function test_construction_payment_create_validation_requires_section_date_and_amount(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('construction-payments.store'), [
                'amount' => '0',
            ])
            ->assertSessionHasErrors(['construction_section_id', 'payment_date', 'amount']);
    }

    public function test_construction_payment_can_be_updated_and_keeps_filter_context(): void
    {
        $user = User::factory()->create();
        $section = ConstructionSection::factory()->create();
        $payment = ConstructionPayment::factory()->forSection($section)->create([
            'payment_date' => '2026-05-18',
            'amount' => 100,
        ]);

        $this->actingAs($user)
            ->put(route('construction-payments.update', $payment), [
                'construction_section_id' => $section->id,
                'payment_date' => '2026-05-19',
                'supplier' => 'ТОО Обновлено',
                'amount' => '650.75',
                'contract' => 'Договор 99',
                'purpose' => 'Обновленная оплата',
                'payment_source' => 'Касса обновлена',
                'filter_construction_section_id' => $section->id,
                'filter_supplier' => 'ТОО Обновлено',
                'filter_per_page' => '50',
                'filter_page' => '2',
            ])
            ->assertRedirect(route('construction-payments.index', [
                'construction_section_id' => $section->id,
                'supplier' => 'ТОО Обновлено',
                'per_page' => '50',
                'page' => '2',
            ]));

        $this->assertDatabaseHas('construction_payments', [
            'id' => $payment->id,
            'construction_section_id' => $section->id,
            'payment_date' => '2026-05-19',
            'supplier' => 'ТОО Обновлено',
            'amount' => '650.75',
            'contract' => 'Договор 99',
            'purpose' => 'Обновленная оплата',
            'payment_source' => 'Касса обновлена',
        ]);
    }

    public function test_construction_payment_update_validation_rejects_invalid_amount(): void
    {
        $user = User::factory()->create();
        $section = ConstructionSection::factory()->create();
        $payment = ConstructionPayment::factory()->forSection($section)->create();

        $this->actingAs($user)
            ->put(route('construction-payments.update', $payment), [
                'construction_section_id' => $section->id,
                'payment_date' => '2026-05-18',
                'amount' => '0',
            ])
            ->assertSessionHasErrors(['amount']);
    }

    public function test_construction_payment_can_be_deleted_and_keeps_query_context(): void
    {
        $user = User::factory()->create();
        $section = ConstructionSection::factory()->create();
        $payment = ConstructionPayment::factory()->forSection($section)->create([
            'payment_date' => '2026-05-18',
            'amount' => 500,
        ]);

        $this->actingAs($user)
            ->delete(route('construction-payments.destroy', $payment), [
                'construction_section_id' => $section->id,
                'date_from' => '2026-05-01',
                'supplier' => $payment->supplier,
                'per_page' => '25',
                'page' => '3',
            ])
            ->assertRedirect(route('construction-payments.index', [
                'construction_section_id' => $section->id,
                'date_from' => '2026-05-01',
                'supplier' => $payment->supplier,
                'per_page' => '25',
                'page' => '3',
            ]));

        $this->assertDatabaseMissing('construction_payments', [
            'id' => $payment->id,
        ]);
    }
}

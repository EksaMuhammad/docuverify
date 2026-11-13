<?php

namespace Tests\Feature;

use App\Livewire\ResearchDashboard;
use App\Models\Document;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VerificationLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ResearchDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_research_dashboard_renders_for_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/research');
        $response->assertStatus(200);
        $response->assertSee('Academic Research');
        $response->assertSee('Confusion Matrix');
        $response->assertSee('System Usability Scale (SUS) Evaluator');
    }

    public function test_sus_score_calculation_logic(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Test with ideal positive SUS scores (odd questions = 5, even questions = 1)
        // Score calculation:
        // Odd questions (1, 3, 5, 7, 9): (5 - 1) * 5 = 20
        // Even questions (2, 4, 6, 8, 10): (5 - 1) * 5 = 20
        // Total = 40 * 2.5 = 100
        Livewire::actingAs($admin)
            ->test(ResearchDashboard::class)
            ->set('susAnswers', [
                1 => 5,
                2 => 1,
                3 => 5,
                4 => 1,
                5 => 5,
                6 => 1,
                7 => 5,
                8 => 1,
                9 => 5,
                10 => 1,
            ])
            ->call('calculateSus')
            ->assertSet('calculatedSusScore', 100.0)
            ->assertSet('susGrade', 'A (Excellent / Highly Acceptable)');
    }

    public function test_csv_export_streams_valid_content(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        VerificationLog::create([
            'document_code' => 'DOC-CSV-TEST01',
            'uploaded_hash' => hash('sha256', 'csv_test'),
            'blockchain_hash' => '0x' . hash('sha256', 'csv_test'),
            'verification_status' => 'VALID',
            'processing_time_ms' => 45,
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($admin)->get('/admin/research/export-verifications');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}

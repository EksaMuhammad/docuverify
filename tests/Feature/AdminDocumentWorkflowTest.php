<?php

namespace Tests\Feature;

use App\Livewire\RegisterDocument;
use App\Models\Document;
use App\Models\User;
use App\Services\BlockchainService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Mockery\MockInterface;
use Tests\TestCase;

class AdminDocumentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_document_registration(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/documents/create');
        $response->assertStatus(200);
        $response->assertSee('Register New Document to Blockchain');
    }

    public function test_document_registration_records_database_and_cleans_temporary_file(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pdfContent = "%PDF-1.4\nTest Admin Upload Content\n%%EOF";
        $fakePdf = UploadedFile::fake()->createWithContent('contract.pdf', $pdfContent);

        $this->mock(BlockchainService::class, function (MockInterface $mock) {
            $mock->shouldReceive('registerDocument')
                ->once()
                ->andReturn([
                    'success' => true,
                    'status' => 'CONFIRMED',
                    'transactionHash' => '0x' . str_repeat('a', 64),
                    'blockNumber' => 12345,
                    'gasUsed' => 45000,
                    'gasPrice' => '1000000000',
                    'network' => 'hardhat_local',
                    'registeredAt' => now()->toIso8601String(),
                ]);
        });

        Livewire::actingAs($admin)
            ->test(RegisterDocument::class)
            ->set('documentName', 'Master Service Agreement 2026')
            ->set('documentCode', 'DOC-20261113-MSA01')
            ->set('file', $fakePdf)
            ->call('register')
            ->assertSet('registrationResult.status', 'CONFIRMED');

        $this->assertDatabaseHas('documents', [
            'document_code' => 'DOC-20261113-MSA01',
            'document_name' => 'Master Service Agreement 2026',
            'registration_status' => 'confirmed',
            'block_number' => 12345,
        ]);

        $this->assertDatabaseHas('transactions', [
            'transaction_status' => 'confirmed',
            'block_number' => 12345,
        ]);
    }

    public function test_document_management_can_filter_and_search(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Document::create([
            'document_code' => 'DOC-SEARCH-001',
            'document_name' => 'Financial Report Q3',
            'document_hash' => hash('sha256', 'report1'),
            'document_id' => '0x' . hash('sha256', 'DOC-SEARCH-001'),
            'registered_by' => $admin->id,
            'registration_status' => 'confirmed',
        ]);

        Document::create([
            'document_code' => 'DOC-OTHER-002',
            'document_name' => 'Employee Handbook',
            'document_hash' => hash('sha256', 'handbook'),
            'document_id' => '0x' . hash('sha256', 'DOC-OTHER-002'),
            'registered_by' => $admin->id,
            'registration_status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get('/admin/documents?search=Financial');
        $response->assertStatus(200);
        $response->assertSee('Financial Report Q3');
        $response->assertDontSee('Employee Handbook');
    }
}

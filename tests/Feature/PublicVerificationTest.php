<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use App\Models\VerificationLog;
use App\Services\BlockchainService;
use App\Services\VerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Mockery\MockInterface;
use Tests\TestCase;

class PublicVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_verify_page_can_be_rendered_without_login(): void
    {
        $response = $this->get('/verify');
        $response->assertStatus(200);
        $response->assertSee('Public Document Verification');
    }

    public function test_verification_produces_valid_status_when_blockchain_hash_matches(): void
    {
        $pdfContent = "%PDF-1.4\nGenuine Test PDF for DocuVerify\n%%EOF";
        $expectedHash = hash('sha256', $pdfContent);
        $docCode = 'DOC-20261113-AUTH01';

        $user = User::factory()->create();
        Document::create([
            'document_code' => $docCode,
            'document_name' => 'Authentic Diploma Certificate',
            'document_hash' => $expectedHash,
            'document_id' => '0x' . hash('sha256', $docCode),
            'registered_by' => $user->id,
            'registration_status' => 'confirmed',
        ]);

        $this->mock(BlockchainService::class, function (MockInterface $mock) use ($docCode, $expectedHash) {
            $mock->shouldReceive('verifyDocument')
                ->once()
                ->with($docCode, $expectedHash)
                ->andReturn([
                    'success' => true,
                    'verificationStatus' => 'VALID',
                    'documentCode' => $docCode,
                    'documentId' => '0x123',
                    'uploadedHash' => $expectedHash,
                    'blockchainHash' => '0x' . $expectedHash,
                    'registeredAt' => now()->toIso8601String(),
                    'registrant' => '0xRegistrarAddress',
                    'message' => 'Document is authentic',
                ]);
        });

        $fakePdf = UploadedFile::fake()->createWithContent('diploma.pdf', $pdfContent);

        $verificationService = app(VerificationService::class);
        $result = $verificationService->verify($docCode, $fakePdf);

        $this->assertEquals('VALID', $result['status']);
        $this->assertEquals($expectedHash, $result['uploadedHash']);

        $this->assertDatabaseHas('verification_logs', [
            'document_code' => $docCode,
            'verification_status' => 'VALID',
        ]);
    }

    public function test_verification_produces_modified_status_when_hash_differs(): void
    {
        $tamperedContent = "%PDF-1.4\nModified Tampered Content\n%%EOF";
        $tamperedHash = hash('sha256', $tamperedContent);
        $originalHash = '0x' . hash('sha256', "%PDF-1.4\nOriginal\n%%EOF");
        $docCode = 'DOC-20261113-MOD01';

        $this->mock(BlockchainService::class, function (MockInterface $mock) use ($docCode, $tamperedHash, $originalHash) {
            $mock->shouldReceive('verifyDocument')
                ->once()
                ->with($docCode, $tamperedHash)
                ->andReturn([
                    'success' => true,
                    'verificationStatus' => 'MODIFIED',
                    'documentCode' => $docCode,
                    'documentId' => '0x456',
                    'uploadedHash' => $tamperedHash,
                    'blockchainHash' => $originalHash,
                    'registeredAt' => now()->toIso8601String(),
                    'registrant' => '0xRegistrarAddress',
                    'message' => 'Document has been modified',
                ]);
        });

        $fakePdf = UploadedFile::fake()->createWithContent('tampered.pdf', $tamperedContent);

        $verificationService = app(VerificationService::class);
        $result = $verificationService->verify($docCode, $fakePdf);

        $this->assertEquals('MODIFIED', $result['status']);

        $this->assertDatabaseHas('verification_logs', [
            'document_code' => $docCode,
            'verification_status' => 'MODIFIED',
        ]);
    }

    public function test_verification_produces_unverifiable_status_when_not_registered_on_chain(): void
    {
        $content = "%PDF-1.4\nUnregistered Document\n%%EOF";
        $hash = hash('sha256', $content);
        $docCode = 'DOC-UNKNOWN-999';

        $this->mock(BlockchainService::class, function (MockInterface $mock) use ($docCode, $hash) {
            $mock->shouldReceive('verifyDocument')
                ->once()
                ->with($docCode, $hash)
                ->andReturn([
                    'success' => true,
                    'verificationStatus' => 'UNVERIFIABLE',
                    'documentCode' => $docCode,
                    'documentId' => '0x789',
                    'uploadedHash' => $hash,
                    'blockchainHash' => null,
                    'message' => 'No record found on blockchain',
                ]);
        });

        $fakePdf = UploadedFile::fake()->createWithContent('unregistered.pdf', $content);

        $verificationService = app(VerificationService::class);
        $result = $verificationService->verify($docCode, $fakePdf);

        $this->assertEquals('UNVERIFIABLE', $result['status']);

        $this->assertDatabaseHas('verification_logs', [
            'document_code' => $docCode,
            'verification_status' => 'UNVERIFIABLE',
        ]);
    }

    public function test_technical_rpc_error_is_never_misclassified_as_unverifiable(): void
    {
        $content = "%PDF-1.4\nSample Content\n%%EOF";
        $hash = hash('sha256', $content);
        $docCode = 'DOC-20261113-ERR01';

        $this->mock(BlockchainService::class, function (MockInterface $mock) use ($docCode, $hash) {
            $mock->shouldReceive('verifyDocument')
                ->once()
                ->with($docCode, $hash)
                ->andReturn([
                    'success' => false,
                    'verificationStatus' => 'TECHNICAL_ERROR',
                    'documentCode' => $docCode,
                    'uploadedHash' => $hash,
                    'message' => 'RPC timeout connecting to Sepolia node',
                ]);
        });

        $fakePdf = UploadedFile::fake()->createWithContent('sample.pdf', $content);

        $verificationService = app(VerificationService::class);
        $result = $verificationService->verify($docCode, $fakePdf);

        // Strict requirement: MUST be TECHNICAL_ERROR, NEVER UNVERIFIABLE
        $this->assertEquals('TECHNICAL_ERROR', $result['status']);
        $this->assertNotEquals('UNVERIFIABLE', $result['status']);

        $this->assertDatabaseHas('verification_logs', [
            'document_code' => $docCode,
            'verification_status' => 'TECHNICAL_ERROR',
        ]);
    }
}


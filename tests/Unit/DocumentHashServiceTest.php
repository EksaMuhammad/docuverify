<?php

namespace Tests\Unit;

use App\Services\DocumentHashService;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\TestCase;

class DocumentHashServiceTest extends TestCase
{
    private DocumentHashService $hashService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hashService = new DocumentHashService();
    }

    public function test_compute_sha256_computes_accurate_hex_hash(): void
    {
        $content = "%PDF-1.4\nTest PDF content for DocuVerify\n%%EOF";
        $tempPath = tempnam(sys_get_temp_dir(), 'test_pdf_');
        file_put_contents($tempPath, $content);

        try {
            $hash = $this->hashService->computeSha256($tempPath);
            $expected = hash('sha256', $content);

            $this->assertEquals($expected, $hash);
            $this->assertEquals(64, strlen($hash));
        } finally {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }

    public function test_normalize_code_trims_and_uppercases(): void
    {
        $this->assertEquals('DOC-20261113-0001', $this->hashService->normalizeCode('  doc-20261113-0001  '));
    }

    public function test_derive_document_id_returns_exact_keccak256_bytes32(): void
    {
        $code = 'DOC-20261113-0001';
        $id = $this->hashService->deriveDocumentId($code);

        $this->assertStringStartsWith('0x', $id);
        $this->assertEquals(66, strlen($id));
        // Matches the ethers.id('DOC-20261113-0001') computed earlier
        $this->assertEquals('0xa844405385313c70dcff705a97704caa323f29b99b1c29a797b1483758445f9c', $id);
    }

    public function test_generate_document_code_format(): void
    {
        $code = $this->hashService->generateDocumentCode();
        $this->assertMatchesRegularExpression('/^DOC-\d{8}-[A-F0-9]{6}$/', $code);
    }

    public function test_validate_pdf_accepts_valid_pdf_header(): void
    {
        $fakePdf = UploadedFile::fake()->createWithContent('document.pdf', "%PDF-1.5\nSample Body\n%%EOF");
        $this->hashService->validatePdf($fakePdf);
        $this->assertTrue(true);
    }

    public function test_validate_pdf_rejects_non_pdf_extension(): void
    {
        $fakeTxt = UploadedFile::fake()->createWithContent('document.txt', "Just plain text");
        $this->expectException(\InvalidArgumentException::class);
        $this->hashService->validatePdf($fakeTxt);
    }

    public function test_validate_pdf_rejects_invalid_magic_bytes(): void
    {
        $fakePdf = UploadedFile::fake()->createWithContent('document.pdf', "NOT_A_PDF_CONTENT");
        $this->expectException(\InvalidArgumentException::class);
        $this->hashService->validatePdf($fakePdf);
    }
}


<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\BlockchainService;

class DocumentController extends Controller
{
    public function __construct(
        protected BlockchainService $blockchainService
    ) {}

    public function show(int $id)
    {
        $document = Document::with(['registrar', 'transactions', 'verificationLogs'])
            ->findOrFail($id);

        // Fetch on-chain record for reconciliation
        $onChainRecord = $this->blockchainService->getDocumentRecord($document->document_code);

        return view('admin.documents.show', compact('document', 'onChainRecord'));
    }
}


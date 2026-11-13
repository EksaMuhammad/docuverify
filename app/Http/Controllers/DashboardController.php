<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\Transaction;
use App\Models\VerificationLog;
use App\Services\BlockchainService;

class DashboardController extends Controller
{
    public function __construct(
        protected BlockchainService $blockchainService
    ) {}

    public function index()
    {
        $totalDocuments = Document::count();
        $confirmedTransactions = Transaction::where('transaction_status', 'confirmed')->count();
        $totalVerifications = VerificationLog::count();
        $validVerifications = VerificationLog::where('verification_status', 'VALID')->count();
        $modifiedDetections = VerificationLog::where('verification_status', 'MODIFIED')->count();
        $unverifiableCount = VerificationLog::where('verification_status', 'UNVERIFIABLE')->count();
        $technicalErrors = VerificationLog::where('verification_status', 'TECHNICAL_ERROR')->count();
        $pendingTransactions = Transaction::where('transaction_status', 'pending')->count();
        $failedTransactions = Transaction::where('transaction_status', 'failed')->count();

        $recentDocuments = Document::with('registrar')
            ->latest()
            ->limit(5)
            ->get();

        $recentVerifications = VerificationLog::latest()
            ->limit(5)
            ->get();

        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->limit(5)
            ->get();

        $blockchainHealth = $this->blockchainService->healthCheck();

        return view('admin.dashboard', compact(
            'totalDocuments',
            'confirmedTransactions',
            'totalVerifications',
            'validVerifications',
            'modifiedDetections',
            'unverifiableCount',
            'technicalErrors',
            'pendingTransactions',
            'failedTransactions',
            'recentDocuments',
            'recentVerifications',
            'recentActivities',
            'blockchainHealth'
        ));
    }
}


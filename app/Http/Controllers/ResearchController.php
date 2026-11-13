<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\VerificationLog;
use Illuminate\Http\Response;

class ResearchController extends Controller
{
    public function exportVerificationCsv()
    {
        $fileName = 'docuverify_verification_benchmarks_' . now()->format('Ymd_His') . '.csv';
        $logs = VerificationLog::latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID',
                'Document Code',
                'Uploaded Hash (SHA-256)',
                'Blockchain Hash',
                'Verification Status',
                'Processing Time (ms)',
                'Timestamp',
            ]);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->id,
                    $log->document_code,
                    $log->uploaded_hash,
                    $log->blockchain_hash ?? 'N/A',
                    $log->verification_status,
                    $log->processing_time_ms,
                    $log->created_at->toIso8601String(),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportTransactionsCsv()
    {
        $fileName = 'docuverify_blockchain_transactions_' . now()->format('Ymd_His') . '.csv';
        $transactions = Transaction::with('document')->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID',
                'Document Code',
                'Transaction Hash',
                'Block Number',
                'Gas Used',
                'Network',
                'Status',
                'Confirmed At',
            ]);

            foreach ($transactions as $tx) {
                fputcsv($handle, [
                    $tx->id,
                    $tx->document?->document_code ?? 'N/A',
                    $tx->transaction_hash,
                    $tx->block_number ?? 'Pending',
                    $tx->gas_used ?? 0,
                    $tx->network,
                    $tx->transaction_status,
                    $tx->confirmed_at?->toIso8601String() ?? 'N/A',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}


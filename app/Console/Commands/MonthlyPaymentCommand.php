<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Iuran;
use App\Models\IuranBulanan;
use App\Models\Params;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MonthlyPaymentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iuran:generate-monthly
                          {--year= : Year to generate (default: current year)}
                          {--month= : Month to generate (default: current month)}
                          {--force : Force regenerate even if records exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly iuran payment records for all active members';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->option('year');
        if (!$year) {
            $year = Carbon::now()->year;
        }

        $month = $this->option('month');
        if (!$month) {
            $month = Carbon::now()->month;
        }

        $force = $this->option('force');

        $this->info("Generating monthly payments for {$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT));

        try {
            // Get current iuran wajib from params
            $iuranWajib = $this->getIuranWajib($year);

            if (!$iuranWajib) {
                $this->error("No active iuran wajib parameter found for year {$year}");
                return 1;
            }

            $this->info("Using iuran wajib: Rp " . number_format($iuranWajib, 0, ',', '.'));

            // Get all active members with their iuran data
            $members = Iuran::with('karyawan')
                           ->where('STATUS_BAYAR', 'AKTIF')
                           ->get();

            if ($members->isEmpty()) {
                $this->warn('No active members found');
                return 0;
            }

            $this->info("Found {$members->count()} active members");

            $created = 0;
            $skipped = 0;
            $updated = 0;

            foreach ($members as $member) {
                try {
                    $result = $this->processMonthlyPayment(
                        $member,
                        $year,
                        $month,
                        $iuranWajib,
                        $force
                    );

                    if ($result === 'created') {
                        $created++;
                    } elseif ($result === 'updated') {
                        $updated++;
                    } elseif ($result === 'skipped') {
                        $skipped++;
                    }

                } catch (\Exception $e) {
                    $this->error("Error processing member {$member->N_NIK}: " . $e->getMessage());
                    Log::error('Monthly payment generation error', [
                        'nik' => $member->N_NIK,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            // Update overdue records
            $this->updateOverdueRecords($year, $month);

            $this->info("\nMonthly payment generation completed:");
            $this->info("- Created & Paid: {$created} records");
            $this->info("- Updated & Paid: {$updated} records");
            $this->info("- Skipped: {$skipped} records");
            $this->info("- All payments: AUTO LUNAS from payroll deduction");

            Log::info('Monthly payment generation completed', [
                'year' => $year,
                'month' => $month,
                'created' => $created,
                'updated' => $updated,
                'skipped' => $skipped,
                'note' => 'All auto-paid from payroll'
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error('Fatal error during monthly payment generation: ' . $e->getMessage());
            Log::error('Monthly payment generation fatal error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Process monthly payment for a single member
     */
    private function processMonthlyPayment(Iuran $member, int $year, int $month, int $iuranWajib, bool $force): string
    {
        $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);
        $iuranSukarela = (int)$member->IURAN_SUKARELA;
        $totalIuran = $iuranWajib + $iuranSukarela;

        // Check if record already exists
        $existing = IuranBulanan::where('N_NIK', $member->N_NIK)
                               ->where('TAHUN', $year)
                               ->where('BULAN', $monthStr)
                               ->first();

        if ($existing) {
            if ($force) {
                // Update existing record - AUTO PAID from payroll
                $existing->update([
                    'IURAN_WAJIB' => $iuranWajib,
                    'IURAN_SUKARELA' => $iuranSukarela,
                    'TOTAL_IURAN' => $totalIuran,
                    'STATUS' => 'LUNAS', // AUTO LUNAS karena dipotong dari payroll
                    'TGL_BAYAR' => Carbon::createFromDate($year, $month, 1)->format('Y-m-d H:i:s'),
                    'UPDATED_BY' => 'PAYROLL_SYSTEM',
                    'UPDATED_AT' => now()
                ]);

                        $namaKaryawan = $member->karyawan ? $member->karyawan->V_NAMA_KARYAWAN : 'N/A';
                $this->line("Updated: {$member->N_NIK} - {$namaKaryawan}");
                return 'updated';
            } else {
                return 'skipped';
            }
        }

        // Create new record - AUTO PAID from payroll
        IuranBulanan::create([
            'N_NIK' => $member->N_NIK,
            'TAHUN' => $year,
            'BULAN' => $monthStr,
            'IURAN_WAJIB' => $iuranWajib,
            'IURAN_SUKARELA' => $iuranSukarela,
            'TOTAL_IURAN' => $totalIuran,
            'STATUS' => 'LUNAS', // AUTO LUNAS karena dipotong dari payroll
            'TGL_BAYAR' => Carbon::createFromDate($year, $month, 1)->format('Y-m-d H:i:s'), // Tanggal 1 setiap bulan
            'CREATED_BY' => 'PAYROLL_SYSTEM',
            'CREATED_AT' => now()
        ]);

        $namaKaryawan = $member->karyawan ? $member->karyawan->V_NAMA_KARYAWAN : 'N/A';
        $this->line("Created & Paid: {$member->N_NIK} - {$namaKaryawan} - Rp " . number_format($totalIuran, 0, ',', '.'));
        return 'created';
    }

    /**
     * Update overdue payment records
     */
    private function updateOverdueRecords(int $year, int $month): void
    {
        $currentDate = Carbon::now();
        $dueDate = Carbon::createFromDate($year, $month, 15); // Due on 15th of each month

        if ($currentDate->isAfter($dueDate)) {
            $overdueCount = IuranBulanan::where('TAHUN', $year)
                                      ->where('BULAN', str_pad($month, 2, '0', STR_PAD_LEFT))
                                      ->where('STATUS', 'BELUM_BAYAR')
                                      ->update([
                                          'STATUS' => 'TERLAMBAT',
                                          'UPDATED_BY' => 'SYSTEM',
                                          'UPDATED_AT' => now()
                                      ]);

            if ($overdueCount > 0) {
                $this->info("- Marked {$overdueCount} records as overdue");
            }
        }
    }

    /**
     * Get current iuran wajib amount
     */
    private function getIuranWajib(int $year)
    {
        $params = Params::where('IS_AKTIF', '1')
                       ->where('TAHUN', $year)
                       ->first();

        return $params ? (int)$params->NOMINAL_IURAN_WAJIB : null;
    }
}
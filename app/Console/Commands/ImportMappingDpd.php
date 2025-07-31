<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MappingDpd;

class ImportMappingDpd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:mapping-dpd {file?} {--truncate : Clear existing data first}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import mapping DPD from CSV file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $csvFile = $this->argument('file') ?? 'Mapping DPD Rev1.csv';
        
        if (!file_exists($csvFile)) {
            $this->error("CSV file not found: {$csvFile}");
            return 1;
        }

        $this->info("Starting import from: {$csvFile}");

        try {
            // Clear existing data if requested
            if ($this->option('truncate') || $this->confirm('Clear existing data first?', true)) {
                MappingDpd::truncate();
                $this->info("✅ Cleared existing data");
            }

            // Read CSV
            $handle = fopen($csvFile, 'r');
            if (!$handle) {
                throw new \Exception("Cannot open CSV file");
            }

            $header = fgetcsv($handle); // Skip header
            $this->info("CSV Headers: " . implode(', ', $header));

            $totalImported = 0;
            $skipped = 0;
            $processed = [];

            $this->info('Processing CSV data...');

            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) >= 3 && !empty($row[1]) && !empty($row[2])) {
                    $dpd = trim($row[1]);
                    $dpw = trim($row[2]);
                    $prefix6 = trim($row[0]);
                    
                    // Skip duplicates
                    if (in_array($dpd, $processed)) {
                        $skipped++;
                        continue;
                    }
                    
                    try {
                        MappingDpd::create([
                            'prefix6' => ($prefix6 === '#N/A' || empty($prefix6)) ? null : $prefix6,
                            'dpd' => $dpd,
                            'dpw' => $dpw
                        ]);
                        
                        $processed[] = $dpd;
                        $totalImported++;
                        
                        // Show progress every 100 records
                        if ($totalImported % 100 == 0) {
                            $this->info("Processed: {$totalImported} records");
                        }
                        
                    } catch (\Exception $insertError) {
                        $this->warn("Skipped duplicate DPD: {$dpd}");
                        $skipped++;
                    }
                } else {
                    $skipped++;
                }
            }

            fclose($handle);

            // Show final results
            $this->info("");
            $this->info("✅ Import completed successfully!");
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("📊 IMPORT SUMMARY:");
            $this->info("   Total imported: " . number_format($totalImported) . " records");
            $this->info("   Skipped/duplicates: " . number_format($skipped) . " records");
            $this->info("   Total processed: " . number_format($totalImported + $skipped) . " records");

            // Show sample data
            $this->info("");
            $this->info("📋 Sample imported data:");
            $samples = MappingDpd::take(5)->get(['dpd', 'dpw']);
            
            if ($samples->count() > 0) {
                $tableData = $samples->map(function($item) {
                    return [$item->dpd, $item->dpw];
                })->toArray();
                
                $this->table(['DPD', 'DPW'], $tableData);
            }

            // Show DPW statistics
            $this->info("📊 Top 10 DPW Distribution:");
            $stats = \DB::table('mapping_dpd')
                ->select('dpw', \DB::raw('COUNT(*) as count'))
                ->groupBy('dpw')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            if ($stats->count() > 0) {
                $statsTable = $stats->map(function($stat) {
                    return [$stat->dpw, number_format($stat->count)];
                })->toArray();
                
                $this->table(['DPW', 'DPD Count'], $statsTable);
            }

            $this->info("");
            $this->info("🎉 Ready for next step!");

        } catch (\Exception $e) {
            $this->error("❌ Import failed: " . $e->getMessage());
            $this->error("🔍 Error details: " . $e->getFile() . " line " . $e->getLine());
            return 1;
        }

        return 0;
    }
}
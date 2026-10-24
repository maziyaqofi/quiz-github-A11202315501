<?php

namespace App\Console\Commands;

use App\Models\DaftarPoli;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:expire-patient-queues')]
#[Description('Expire unfinished patient queues after their scheduled end time')]
class ExpirePatientQueues extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $expiredCount = 0;

        // Find queues that may have passed their scheduled end time
        DaftarPoli::with('jadwalPeriksa')
            ->whereIn('status_antrian', ['menunggu', 'dilewati'])
            ->whereDate('tanggal_kunjungan', '<=', $now->toDateString())
            ->chunkById(100, function ($queues) use ($now, &$expiredCount) {
                foreach ($queues as $queue) {
                    if (
                        $queue->tanggal_kunjungan === null ||
                        $queue->jadwalPeriksa === null
                    ) {
                        continue;
                    }

                    // Combine the visit date with the doctor's end time
                    $scheduledEnd = Carbon::parse(
                        $queue->tanggal_kunjungan->toDateString()
                        . ' '
                        . $queue->jadwalPeriksa->jam_selesai
                    );

                    if ($now->greaterThanOrEqualTo($scheduledEnd)) {
                        // Update only queues that are still unfinished
                        $updated = DaftarPoli::whereKey($queue->id)
                            ->whereIn('status_antrian', ['menunggu', 'dilewati'])
                            ->update([
                                'status_antrian' => 'kedaluwarsa',
                            ]);

                        $expiredCount += $updated;
                    }
                }
            });

        $this->info("Expired patient queues: {$expiredCount}");

        return self::SUCCESS;
    }
}
<?php

use App\Console\Commands\ExportFormData;
use App\Console\Commands\Processor;
use Illuminate\Support\Facades\Schedule;

Schedule::command(ExportFormData::class, ['-Q'])->dailyAt('00:00:00');
Schedule::command(Processor::class)->hourlyAt(40);

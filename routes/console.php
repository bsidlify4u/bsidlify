<?php

use Illuminate\Foundation\Inspiring;
use App\Facades\Bsidlify;

Bsidlify::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

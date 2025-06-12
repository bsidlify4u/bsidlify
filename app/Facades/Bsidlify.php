<?php

namespace App\Facades;

use Illuminate\Support\Facades\Artisan;

/**
 * @method static int|null call(string $command, array $parameters = [], $outputBuffer = null)
 * @method static \Symfony\Component\Console\Output\OutputStyle output()
 * @method static string queue(string $command, array $parameters = [])
 * @method static array all()
 * @method static string output(string $command, array $parameters = [])
 * @method static void terminus(callable $callback = null)
 * @method static \Symfony\Component\Console\Command\Command command(string $command, \Closure $callback)
 *
 * @see \Illuminate\Contracts\Console\Kernel
 */
class Bsidlify extends Artisan
{
    // This class extends the Artisan facade and adds no additional functionality
    // It serves as an alias for Artisan to maintain consistent Bsidlify branding
} 
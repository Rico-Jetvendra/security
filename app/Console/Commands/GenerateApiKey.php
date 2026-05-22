<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class GenerateApiKey extends Command{
    protected $signature = 'api:key';
    protected $description = 'Generate secure API key';

    public function handle(){
        $apiKey  = bin2hex(random_bytes(32));
        $hashKey = Hash::make($apiKey);

        $this->info('API Key Generated:');
        $this->line($apiKey);
        $this->info('-----------------------------');
        $this->info('Hash Key Generated:');
        $this->line($hashKey);
        return 0;
    }
}

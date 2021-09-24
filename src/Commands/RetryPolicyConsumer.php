<?php

namespace Daalvand\RetryPolicy\Commands;

use Daalvand\RetryPolicy\Facades\RetryContext;
use Illuminate\Console\Command;

class RetryPolicyConsumer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'retry_policy:consumer  {--queues= : The names of the queues to work}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'retry policy consumer';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $queues = $this->option('queues') ? explode(',', $this->option('queues')) : null;
        RetryContext::consume($queues);
    }
}

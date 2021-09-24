<?php
/** @noinspection UnserializeExploitsInspection */

namespace Daalvand\RetryPolicy\Commands;

use Daalvand\RetryPolicy\Facades\RetryContext;
use Daalvand\RetryPolicy\Facades\Serializer as SerializerFacade;
use Daalvand\RetryPolicy\Models\RetryPolicyFailedJob;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class RetryPolicyRequeue extends Command
{
    private const CHUNK_COUNT = 10;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'retry_policy:requeue 
                                                {--queues= : The names of the queues to requeue}
                                                {--ids= : The ids to requeue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'retry policy requeue failed jobs';

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
     * @throws Exception
     */
    public function handle()
    {

        if($this->option('ids')){
            $query = RetryPolicyFailedJob::query()->whereIn('id', explode(',', $this->option('ids')));
        }else{
            $query = RetryPolicyFailedJob::canRequeue();
        }
        if($this->option('queues')){
            $query->whereIn('queue', explode(',', $this->option('queues')));
        }

        /**  @var RetryPolicyFailedJob $failedJob */
        $query->chunk(self::CHUNK_COUNT, function (Collection $data){
            $retryables = [];
            foreach ($data as $datum) {
                $retryables[] = SerializerFacade::unserialize($datum->payload);
            }
            RetryPolicyFailedJob::query()->whereIn('id', $data->pluck('id'))->delete();
            RetryContext::perform($retryables);
        });

    }
}

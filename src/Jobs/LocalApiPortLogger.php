<?php

namespace Knighttower\Toolbox\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;

class LocalApiPortLogger implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
    * data
     *
    * @var object $data
    */
    public $data;

    /**
     * Create a new job instance.
     *
     * @param Array $data
     * @return void
     */
    public function __construct($data)
    {
        $this->data = (object) $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $callInfo = $this->data->callInfo;
        $method = $this->data->method;

        foreach ($callInfo as $key => $info) {
            if ($info instanceof \Exception) {
                $callInfo[$key] = 'DATA: -->' . $info->getMessage() . "\r\n";
            }

            $callInfo[$key] = 'DATA: -->' . var_export($info, true) . "\r\n";
        }

        $callInfo = ":: ($method) CALL INFO :: \r\n" . implode('<----->', $callInfo) . "\r\n";

        switch ($this->data->type) {
            case 'log':
                \LogInfo::write($callInfo, 'LOCAL-API-PORT--LOG');
                break;

            case 'success':
                \LogInfo::write($callInfo, 'LOCAL-API-PORT--SUCCESS');
                break;

            case 'error':
                \LogInfo::error(new \Exception($callInfo), 'LOCAL-API-PORT--ERRORS');
                break;
        }
    }
}

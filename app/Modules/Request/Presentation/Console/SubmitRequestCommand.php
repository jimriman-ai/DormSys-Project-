<?php

declare(strict_types=1);

namespace App\Modules\Request\Presentation\Console;

use App\Modules\Request\Application\Services\SubmitRequestAction;
use App\Modules\Request\Domain\ValueObjects\RequestId;
use Illuminate\Console\Command;

class SubmitRequestCommand extends Command
{
    protected $signature = 'request:submit {request_id : Request UUID}';

    protected $description = 'Submit a draft request for approval.';

    public function handle(SubmitRequestAction $action): int
    {
        $request = $action->execute(RequestId::fromString((string) $this->argument('request_id')));

        $this->info('Request submitted: '.$request->requireId()->value);
        $this->line('Status: '.$request->status);

        return self::SUCCESS;
    }
}

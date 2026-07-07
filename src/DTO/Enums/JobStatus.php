<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\DTO\Enums;

/** Mirrors `JobStatus` in the Print Agent (src/queue/print-job.types.ts). */
enum JobStatus: string
{
    case Pending = 'pending';
    case Queued = 'queued';
    case Rendering = 'rendering';
    case Printing = 'printing';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
}

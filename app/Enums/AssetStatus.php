<?php

namespace App\Enums;

enum AssetStatus: string
{
    case QUEUED = 'queued';

    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'done';
    case FAILED = 'failed';
}

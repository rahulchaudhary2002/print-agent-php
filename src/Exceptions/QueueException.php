<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\Exceptions;

/** A queue operation (pause/resume/clear) or job submission was rejected because the queue is full/paused/stopped. */
class QueueException extends PrintAgentException {}

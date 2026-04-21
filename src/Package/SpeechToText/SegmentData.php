<?php

declare(strict_types=1);

namespace Package\Audio\SpeechToText;

class SegmentData
{
    public function __construct(
        public readonly int $segment,
        public readonly int $startTimestamp,
        public readonly int $endTimestamp,
        public readonly string $text
    ) {}
}

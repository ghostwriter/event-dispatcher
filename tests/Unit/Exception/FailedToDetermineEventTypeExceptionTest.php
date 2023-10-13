<?php

declare(strict_types=1);

namespace Ghostwriter\EventDispatcher\Tests\Unit\Exception;

use Ghostwriter\EventDispatcher\Exception\FailedToDetermineEventTypeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FailedToDetermineEventTypeException::class)]
final class FailedToDetermineEventTypeExceptionTest extends TestCase
{
    public function testExample(): void
    {
        $this->assertTrue(true);
    }
}


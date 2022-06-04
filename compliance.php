<?php

declare(strict_types=1);

use Ghostwriter\Compliance\Configuration\ComplianceConfiguration;
use Ghostwriter\Compliance\ValueObject\PhpVersion;
use Ghostwriter\Compliance\ValueObject\Tool;

return static function (ComplianceConfiguration $complianceConfiguration): void {
//    $complianceConfig->phpVersion(PhpVersion::CURRENT_STABLE);
    $complianceConfiguration->phpVersion(PhpVersion::CURRENT_LATEST);
};

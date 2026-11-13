<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum MagazineIssueCopyType: string
{
    case DIGITAL = 'Digital';
    case PRINTED_ORIGINAL = 'Printed-Original';
    case PRINTED_COPY = 'Printed-Copy';
}

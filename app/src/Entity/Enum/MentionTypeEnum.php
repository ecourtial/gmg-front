<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum MentionTypeEnum: string
{
    case ADVERTISEMENT = 'Advertisement';
    case COMPARISON = 'Comparison';
    case GUIDE = 'Guide';
    case MENTION = 'Mention';
    case PLAYABLE_DEMO = 'Playable-demo';
    case OTHER = 'Other';
    case PREVIEW = 'Preview';
    case SHORT_PREVIEW = 'Short-preview';
    case SHORT_TEST = 'Short-test';
    case TEST = 'Test';
    case WATCHABLE_DEMO = 'Watchable-demo';
}

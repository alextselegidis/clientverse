<?php

/* ----------------------------------------------------------------------------
 * Clientverse - Self-Hosted CRM
 *
 * @package     Clientverse
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://clientverse.org
 * ---------------------------------------------------------------------------- */

namespace App\Enums;

enum ProjectStatusEnum: string
{
    case PLANNED = 'planned';
    case ACTIVE = 'active';
    case ON_HOLD = 'on_hold';
    case COMPLETED = 'completed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function default(): string
    {
        return self::PLANNED->value;
    }

    public static function labels(): array
    {
        return [
            'planned' => __('planned'),
            'active' => __('active'),
            'on_hold' => __('on_hold'),
            'completed' => __('completed'),
        ];
    }
}

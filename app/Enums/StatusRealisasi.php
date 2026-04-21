<?php

namespace App\Enums;

enum StatusRealisasi: string
{
    case DRAF = 'DRAF';
    case SUBMITTED = 'SUBMITTED';
    case REJECTED = 'REJECTED';
    case APPROVED = 'APPROVED';
}

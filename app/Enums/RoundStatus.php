<?php

namespace App\Enums;

enum RoundStatus: string
{
    case Voting = 'voting';
    case Revealed = 'revealed';
}

<?php

namespace App\Domain\Engagement\Enums;

enum ReactionType: string
{
    case Like = 'like';
    case Love = 'love';
    case Laugh = 'laugh';
    case Wow = 'wow';
    case Sad = 'sad';
    case Angry = 'angry';
}

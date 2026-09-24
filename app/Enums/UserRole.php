<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'ADMIN';
    case Teacher = 'TEACHER';
    case Student = 'STUDENT';
    case Counselor = 'COUNSELOR';
}

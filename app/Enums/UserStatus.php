<?php

namespace App\Enums;

enum UserStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Deactive = 'deactive';
    case Blocked = 'blocked';
}

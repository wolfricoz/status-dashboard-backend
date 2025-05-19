<?php

namespace App\Enum;

enum ServiceStatusType: string
{
	case OK = 'OK';
	case OFFLINE = 'OFFLINE';
	case WARNING = 'WARNING';
	case ERROR = 'ERROR';
	case UNKNOWN = 'UNKNOWN';
}
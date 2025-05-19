<?php

namespace App\Message;

final class GetServiceStatus
{
    /*
     * Add whatever properties and methods you need
     * to hold the data for this message class.
     */

     public function __construct(
         public readonly string $name,
     ) {
			 echo 'GetServiceStatus started' . PHP_EOL;
     }
}

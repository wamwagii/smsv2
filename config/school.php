<?php

return [
    'name'    => env('SCHOOL_NAME', 'School Management System'),
    'motto'   => env('SCHOOL_MOTTO', 'Excellence in Education'),
    'address' => env('SCHOOL_ADDRESS', 'P.O. Box 4908-01002, Nairobi, Kenya'),
    'phone'   => env('SCHOOL_PHONE', '+254 717 340 777'),
    'email'   => env('SCHOOL_EMAIL', 'info@wiredwise.co.ke'),

    /*
    |--------------------------------------------------------------------------
    | SMS audience
    |--------------------------------------------------------------------------
    | SMS is only ever sent to a student's father or mother.
    | Guardians (uncles, aunts, sponsors) are never contacted by SMS,
    | even if they are linked to a student and have opted in.
    */
    'sms_relationships' => ['father', 'mother'],
];
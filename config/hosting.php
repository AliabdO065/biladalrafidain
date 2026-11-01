<?php
return [
    'mode' => env('HOSTING_MODE', 'shared'),
    'jobs_per_tick' => (int) env('HOSTING_JOBS_PER_TICK', 20),
    'worker_seconds' => (int) env('HOSTING_WORKER_SECONDS', 50),
];
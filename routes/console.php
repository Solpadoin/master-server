<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
*/

// Clean up stale servers (no heartbeat for > timeout period)
Schedule::command('servers:cleanup-stale')->everyMinute();

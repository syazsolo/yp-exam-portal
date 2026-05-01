<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('exams:activate')->everyMinute();
Schedule::command('exams:close')->everyMinute();

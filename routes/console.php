<?php

    use App\Console\Commands\CheckSubscriptionExpiry;
    use App\Console\Commands\ClearLogFiles;
    use App\Console\Commands\ExpireQuotations;
    use App\Console\Commands\ExpireStocks;
    use App\Console\Commands\SendSubscriptionReminders;
    use App\Console\Commands\UpdatePreOrderStock;
    use App\Enums\Status;
    use App\Models\BusinessOnBoard;

    Schedule::command( ClearLogFiles::class )->daily();

    Schedule::command( SendSubscriptionReminders::class )->daily();

    Schedule::command( ExpireStocks::class )->everyMinute()->withoutOverlapping();

    Schedule::command( ExpireQuotations::class )
            ->everyMinute()
            ->withoutOverlapping();

    Schedule::command( CheckSubscriptionExpiry::class )
            ->everyThirtyMinutes()
            ->withoutOverlapping();

    Schedule::command( UpdatePreOrderStock::class )
            ->everyMinute()
            ->withoutOverlapping();

    Schedule::call( function () {
        BusinessOnBoard::where( 'status' , Status::INACTIVE )->delete();
    } )->daily();
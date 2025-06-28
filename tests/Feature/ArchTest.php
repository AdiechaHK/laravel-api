<?php

declare(strict_types=1);

arch()
    ->expect('App')
    ->not->toUse(['die', 'dd', 'dump']);


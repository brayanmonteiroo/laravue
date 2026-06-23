<?php

test('horizon snapshot está registrado no schedule', function () {
    $this->artisan('schedule:list')
        ->assertSuccessful()
        ->expectsOutputToContain('horizon:snapshot');
});

<?php

use App\Models\User;
use Laravel\Dusk\Browser;

test('basic example', function () {

    $this->browse(function (Browser $browser) {

        $browser->visit('/')
            ->assertSee('Kifaa');

    });

});

test('user can upload cv file', function () {

    $user = User::factory()->create();

    $this->browse(function (Browser $browser) use ($user) {

        $browser->loginAs($user)

            ->visit(route('cv.upload.form'))

            ->attach(
                'resume',
                base_path('tests/Fixtures/sample_cv.pdf')
            )

            ->press('Generate My Twin')
            ->pause(5000)

            ->assertPathIs('/cv/preview');

    });

});
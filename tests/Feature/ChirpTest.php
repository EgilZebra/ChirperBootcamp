<?php

use App\Events\ChirpCreater;
use App\Models\User;
use App\Models\Chirp;
use App\Notifications\NewChirp;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Mockery;

test('test example', function () {
    expect(true)->toBeTrue();

    $response = $this->get('/');

    $response->assertStatus(200);
});

test('test chirper functionality', function ($message) {
   Event::fake();

    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post('/chirps', ['message' => $message]);

    $response
        ->assertSessionHasNoErrors()
        ->assertStatus(302);

    Event::assertDispatched((ChirpCreater::class));
})->with([
    'test'
]);

test('chirper message formation', function ($message) {
    Event::fake();

    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post('/chirps', ['message' => $message]);

    $response
        ->assertSessionHasErrors()
        ->assertStatus(302); 
    
    
})->with([
    234,
    '',
    'sefssefsefsfesfsfsfesfsefsefsefsefseffesfsefsefsefsefsefsefsefsefsefsef
    sefssefsefsfesfsfsfesfsefsefsefsefseffesfsefsefsefsefsefsefsefsefsefsef
    sefssefsefsfesfsfsfesfsefsefsefsefseffesfsefsefsefsefsefsefsefsefsefsef
    sefssefsefsfesfsfsfesfsefsefsefsefseffesfsefsefsefsefsefsefsefsefsefsef
    sefssefsefsfesfsfsfesfsefsefsefsefseffesfsefsefsefsefsefsefsefsefsefsef'
]);

test('edit chirper funtionality', function ($message) {
    Event::fake();

    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->post('/chirps', ['message' => 'test hello']);

    Event::assertDispatched((ChirpCreater::class));
    
    $chirp = Chirp::where('user_id', $user->id)->latest()->first();
    $chirpId = $chirp->id;
    
    $response = $this
        ->actingAs($user)
        ->patch('/chirps/' . $chirpId, [
            'message' => $message,
    ]);

    $response
         ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('chirps', ['message' => $message]);
})->with([
    'test goodbye'
]);;

test('delete chirp functionality', function ($message) {
    Event::fake();

    $first_user = User::factory()->create();
    $second_user = User::factory()->create();

    $this
        ->actingAs($first_user)
        ->post('/chirps', ['message' => $message]);

    $chirp = Chirp::where('user_id', $first_user->id)->latest()->first();
    $chirpid = $chirp->id;

    $first_response = $this
        ->actingAs($second_user)
        ->delete('/chirps/' . $chirpid );

    $first_response
        ->assertStatus(403);

    $this->assertDatabaseHas('chirps', ['message' => $message]);

    $second_response = $this
        ->actingAs($first_user)
        ->delete('/chirps/' . $chirpid);

    $second_response
        ->assertStatus(302)
        ->assertSessionHasNoErrors();

    $this->assertDatabaseMissing('chirps', ['message' => $message]);
})->with([
    'test delete'
]);;

test('the notifications functionality', function ($message) {

    Notification::fake();

    $user = User::factory()->create();
    $second_user = User::factory()->create();
   
    $mockChirp = Mockery::mock(Chirp::class);
    $mockChirp->shouldReceive('create')->andReturn(new Chirp([
        'message' => $message,
    ]));
     
    $this
        ->actingAs($user)
        ->post('/chirps', ['message' => $message]);

    
    Notification::assertSentTo(
        [$second_user],
        NewChirp::class,
        function ($notification) {
            return method_exists($notification, 'toMail');
        }
    );
    
})->with([
    'test notification'
]);
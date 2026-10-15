<?php

use App\Models\Slider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('a cropped slider image can be uploaded', function () {
    Storage::fake('public');

    $this->actingAs(User::factory()->create())
        ->post(route('sliders.store'), [
            'image' => UploadedFile::fake()->image('banner-cropped.jpg', 1600, 700),
            'status' => 1,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('sliders.index'));

    $slider = Slider::query()->sole();
    Storage::disk('public')->assertExists($slider->image);
});

test('a slider image can be replaced and retained when no replacement is submitted', function () {
    Storage::fake('public');
    $original = UploadedFile::fake()->image('original.jpg')->store('sliders', 'public');
    $slider = Slider::query()->create(['image' => $original, 'status' => 1]);
    $this->actingAs(User::factory()->create());

    $this->post(route('sliders.update', $slider), [
        'image' => UploadedFile::fake()->image('replacement-cropped.jpg', 1600, 700),
        'status' => 1,
    ])->assertSessionHasNoErrors()->assertRedirect(route('sliders.index'));

    $replacement = $slider->fresh()->image;
    expect($replacement)->not->toBe($original);
    Storage::disk('public')->assertMissing($original);
    Storage::disk('public')->assertExists($replacement);

    $this->post(route('sliders.update', $slider), [
        'button' => 'Shop now',
        'status' => 1,
    ])->assertSessionHasNoErrors()->assertRedirect(route('sliders.index'));

    expect($slider->fresh()->image)->toBe($replacement);
    Storage::disk('public')->assertExists($replacement);
});

test('slider images must fit the two megabyte upload limit', function () {
    Storage::fake('public');

    $this->actingAs(User::factory()->create())
        ->from(route('sliders.create'))
        ->post(route('sliders.store'), [
            'image' => UploadedFile::fake()->image('banner.png')->size(2049),
            'status' => 1,
        ])
        ->assertSessionHasErrors('image');

    expect(Slider::query()->count())->toBe(0);
    Storage::disk('public')->assertDirectoryEmpty('sliders');
});

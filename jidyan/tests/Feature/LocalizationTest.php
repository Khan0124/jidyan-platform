<?php

it('allows visitors to switch between supported locales', function () {
    $this->from(route('home'))
        ->post(route('locale.update'), ['locale' => 'ar'])
        ->assertRedirect(route('home'))
        ->assertSessionHas('status', __('Language updated.'));

    expect(session('locale'))->toBe('ar');

    $this->get(route('home'))
        ->assertSee('اكتشف المواهب الكروية');
});

it('rejects unsupported locales', function () {
    $this->from(route('home'))
        ->post(route('locale.update'), ['locale' => 'fr'])
        ->assertRedirect(route('home'))
        ->assertSessionHasErrors('locale');

    expect(session('locale'))->not->toBe('fr');
});

<?php

test('returns a successful response', function () {
    $response = $this->get(route('shop.home'));

    $response->assertStatus(200);
});
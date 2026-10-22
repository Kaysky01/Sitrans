<?php

it('returns a successful response from the landing page', function () {
    $this->get('/')->assertStatus(200);
});

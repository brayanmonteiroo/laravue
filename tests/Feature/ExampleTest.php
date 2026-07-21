<?php

test('retorna resposta com sucesso', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
});

<?php

/**
 * @var MBIct\Factory\Factory $factory
 */

$factory->define(TestModelA::class, function ($faker) {
    return [
        'id'   => 123,
        'name' => 'test',
    ];
});
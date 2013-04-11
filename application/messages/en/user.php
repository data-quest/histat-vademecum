<?php

defined('SYSPATH') or die('No direct access allowed.');

return array(
    'username' => array(
        'not_empty' => 'user name was not accepted.',
        'min_length' => 'user name should contain at least :param2 characters.',
        'max_length' => 'user name should consist of a maximum of :param2 characters',
        'unique' => 'this username already exists.',
        'alpha_dash' => 'user name should contain only numbers, characters, and underscore (underline).',
    ),
    'name' => array(
        'not_empty' => 'First name is not specified.',
        'name' => 'specified first name is not valid'
    ),
    'surname' => array(
        'not_empty' => 'Second name / Family name is not specified.',
        'name' => 'specified second name / family name is not valid.'
    ),
    'street' => array(
        'not_empty' => 'Street, House Nr., or Post Box are not specified.',
    ),
    'zip' => array(
        'not_empty' => 'Postal code is not specified.',
        'numeric' => 'specified postal code is not valid'
    ),
    'location' => array(
        'not_empty' => 'City is not specified',
        'alpha' => 'Specified city is not valid'
    ),
    'country' => array(
        'not_empty' => 'Country is not specified',
        'alpha' => 'Specified country is not valid'
    ),
    'email' => array(
        'not_empty' => 'e-mail address is not specified',
        'email' => 'specified e-mail address is not valid',
        'unique' => 'e-mail address already exists',
    ),
);
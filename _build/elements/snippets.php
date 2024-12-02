<?php

return [
    'TicketForm' => [
        'file' => 'ticket_form',
        'description' => '',
        'properties' => include('properties/properties.ticket_form.php'),
    ],
    'TicketComments' => [
        'file' => 'comments',
        'description' => '',
        'properties' => include('properties/properties.comments.php'),
    ],
    'TicketLatest' => [
        'file' => 'ticket_latest',
        'description' => '',
        'properties' => include('properties/properties.ticket_latest.php'),
    ],
    'TicketMeta' => [
        'file' => 'ticket_meta',
        'description' => '',
        'properties' => include('properties/properties.ticket_meta.php'),
    ],
    'getTickets2' => [
        'file' => 'get_tickets2',
        'description' => '',
        'properties' => include('properties/properties.get_tickets2.php'),
    ],
    'getTickets2Sections' => [
        'file' => 'get_sections',
        'description' => '',
        'properties' => include('properties/properties.get_sections.php'),
    ],
    'getComments' => [
        'file' => 'get_comments',
        'description' => '',
        'properties' => include('properties/properties.get_comments.php'),
    ],
    'getStars' => [
        'file' => 'get_stars',
        'description' => '',
        'properties' => include('properties/properties.get_stars.php'),
    ],
    'subscribeAuthor' => [
        'file' => 'subscribe_author',
        'description' => '',
        'properties' => include('properties/properties.subscribe_author.php'),
    ],
]; 
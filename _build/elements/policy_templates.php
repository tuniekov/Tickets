<?php

return [
    'Tickets2UserPolicyTemplate' => [
        'description' => 'A policy for users to create Tickets2 and comments.',
        'template_group' => 1,
        'permissions' => [
            'ticket_delete' => [],
            'ticket_publish' => [],
            'ticket_save' => [],
            'ticket_view_private' => [],
            'ticket_vote' => [],
            'ticket_star' => [],
            'section_unsubscribe' => [],
            'comment_save' => [],
            'comment_delete' => [],
            'comment_remove' => [],
            'comment_publish' => [],
            'comment_file_upload' => [],
            'comment_vote' => [],
            'comment_star' => [],
            'ticket_file_upload' => [],
            'ticket_file_delete' => [],
            'thread_close' => [],
            'thread_delete' => [],
            'thread_remove' => [],
        ],
    ],
    'Tickets2SectionPolicyTemplate' => [
        'description' => 'A policy for users to add Tickets2 to section.',
        'template_group' => 3,
        'permissions' => [
            'section_add_children' => [],
        ],
    ],
]; 
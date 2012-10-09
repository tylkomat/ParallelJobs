<?php

return array(
    'fork_manager' => array(
        'share_result' => true,
        'auto_start' => true,
        'container' => 'fork_manager_file_container',
    ),
    'fork_manager_segment_container' => array(
        'identifier' => 'Z',
    ),
    'fork_manager_file_container' => array(
        'dir' => __DIR__ . '/../src/ParallelJobs/System/Fork/Storage/tmp',
    ),
    'fork_manager_memcached_container' => array(
        'config' => array(
            'host' => '127.0.0.1',
            'port' => 11211,
        ),
    ),
);

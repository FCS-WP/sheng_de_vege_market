<?php
function enqueue_sprintf_for_elementor()
{
    if (did_action('elementor/loaded')) {
        wp_enqueue_script(
            'sprintf-js',
            'https://cdnjs.cloudflare.com/ajax/libs/sprintf/1.1.2/sprintf.min.js',
            [],
            '1.1.2',
            true
        );
    }
}
add_action('elementor/editor/after_enqueue_scripts', 'enqueue_sprintf_for_elementor');

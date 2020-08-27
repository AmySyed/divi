<?php

class Dipe_Tag {

    public $plugin_slug = 'cf7-styler-for-divi';

    public function __construct() {
        add_action( 'wpcf7_admin_init', [$this, 'tag_generator'], 15 );
    }

    public function tag_generator() {

        $tag_generator = \WPCF7_TagGenerator::get_instance();
        $callback = 'jj';
        $tag_generator->add('dipe_row', __('row', 'dvppl-cf7-styler'), $callback, $this->plugin_slug);
        $tag_generator->add('dipe_one', __('1-col', 'dvppl-cf7-styler'), $callback, $this->plugin_slug);
        $tag_generator->add('dipe_one_half', __('1/2-col', 'dvppl-cf7-styler'), $callback, $this->plugin_slug);
        $tag_generator->add('dipe_one_third', __('1/3-col', 'dvppl-cf7-styler'), $callback, $this->plugin_slug);
        $tag_generator->add('dipe_one_fourth', __('1/4-col', 'dvppl-cf7-styler'), $callback, $this->plugin_slug);
        $tag_generator->add('dipe_two_third', __('2/3-col', 'dvppl-cf7-styler'), $callback, $this->plugin_slug);
        $tag_generator->add('dipe_three_fourth', __('3/4-col', 'dvppl-cf7-styler'), $callback, $this->plugin_slug);

    }

}

new Dipe_Tag;
<?php

class Origamiez_Widget_Social_Links extends WP_Widget {

    public $icon = 'ti-sharethis';

    function __construct() {
        $widget_ops  = array('classname' => 'origamiez-widget-social-links', 'description' => __('Display your social links. Config on Theme Options >> Social links.', 'origamiez'));
        $control_ops = array('width' => 'auto', 'height' => 'auto');
        parent::__construct('origamiez-widget-social-links', __('Origamiez Social Links', 'origamiez'), $widget_ops, $control_ops);
    }

    function update($new_instance, $old_instance) {
        $instance          = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }

    function widget($args, $instance) {
        extract($args);

        $instance = wp_parse_args((array) $instance, $this->get_default());
        $title    = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);

        echo wp_kses_post($before_widget);
        if (!empty($title))
            echo wp_kses_post($before_title . $title . $after_title);

        $socials = ot_get_option('social_links', array());

        if (!empty($socials)):
            ?>
            <div class="social-link-inner clearfix">
                <?php
                foreach ($socials as $social):                    
                    $style = '';
                    if ($social['color']) {
                        $style = sprintf('style="color:#FFF; background-color:%1$s; border-color: %1$s;"', $social['color']);
                    }

                    if ('fa fa-rss' == $social['icon'] && empty($social['href'])){
                        $social['href'] = get_bloginfo('rss2_url');
                    }
                    ?>
                    <a href="<?php echo esc_url($social['href']); ?>" data-placement="top"  data-toggle="tooltip" title="<?php echo esc_attr($social['title']);?>" rel="nofollow" target="_blank" class="origamiez-tooltip social-link social-link-first" <?php echo wp_kses_post($style);?>><span class="<?php echo esc_attr($social['icon']); ?>"></span></a>
                    <?php
                endforeach;
                ?>
            </div>
            <?php
        endif;

        echo wp_kses_post($after_widget);
    }

    function form($instance) {
        $instance = wp_parse_args((array) $instance, $this->get_default());
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:', 'origamiez'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['title'])); ?>" />
        </p>   
        <?php
    }

    protected function get_default() {
        return array(
            'title' => __('Social Links', 'origamiez')
        );
    }

}

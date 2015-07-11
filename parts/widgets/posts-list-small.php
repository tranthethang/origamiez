<?php

add_action('widgets_init', array('Origamiez_Widget_Posts_List_Small', 'register'));

class Origamiez_Widget_Posts_List_Small extends CT_Post_Widget {

    public static function register(){
        register_widget('Origamiez_Widget_Posts_List_Small');
    }

    function __construct() {
        $widget_ops  = array('classname' => 'origamiez-widget-posts-small-thumbnail', 'description' => __('Display posts list with small thumbnail.', 'origamiez'));
        $control_ops = array('width' => 'auto', 'height' => 'auto');
        parent::__construct('origamiez-widget-post-list-small', __('Origamiez Posts List Small', 'origamiez'), $widget_ops, $control_ops);
    }

    function widget($args, $instance) {
        extract($args);

        $instance = wp_parse_args((array) $instance, $this->get_default());

        extract($instance);

        $title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);

        echo  htmlspecialchars_decode(esc_html($before_widget));
        if (!empty($title))
            echo  htmlspecialchars_decode(esc_html($before_title . $title . $after_title));

        $query = $this->get_query($instance);
        $posts = new WP_Query($query);

        if ($posts->have_posts()):
            $loop_index = 0;
            while ($posts->have_posts()):
                $posts->the_post();
                $post_title = get_the_title();
                $post_url   = get_permalink();

                $post_classes = array('origamiez-wp-mt-post', 'clearfix');
                if (0 == $loop_index) {
                    $post_classes[] = 'origamiez-wp-mt-post-first';
                }
                ?>
                <div <?php post_class($post_classes); ?>>
                    <?php if (has_post_thumbnail()): ?>
                        <a href="<?php echo esc_url($post_url); ?>" title="<?php echo esc_attr($post_title); ?>" class="link-hover-effect origamiez-post-thumb pull-left">                    
                            <?php the_post_thumbnail('origamiez-square-xs', array('class' => 'image-effect img-responsive')); ?>                        
                        </a>
                    <?php endif; ?>

                    <div class="origamiez-wp-mt-post-detail">
                        <h5>                                                
                            <a class="entry-title" href="<?php echo esc_url($post_url); ?>" title="<?php echo esc_attr($post_title); ?>"><?php echo esc_attr($post_title); ?></a>
                        </h5>

                        <?php if($is_show_date || $is_show_comments): ?>
                            <p class="metadata">
                                <?php get_template_part('parts/metadata/author'); ?>

                                <?php if($is_show_date): ?>
                                    <?php get_template_part('parts/metadata/date'); ?>                                
                                <?php endif;?>
                                
                                <?php if($is_show_date && $is_show_comments): ?>
                                    <?php get_template_part('parts/metadata/divider'); ?>
                                <?php endif;?>

                                <?php if($is_show_comments): ?>
                                    <?php get_template_part('parts/metadata/comments'); ?>
                                <?php endif;?>        
                            </p> 
                        <?php endif;?>
                                                                                                           
                    </div>                                                
                </div>
                <?php
                $loop_index++;
            endwhile;
        endif;
        wp_reset_postdata();

        echo  htmlspecialchars_decode(esc_html($after_widget));
    }

    function update($new_instance, $old_instance) {
        $instance = parent::update($new_instance, $old_instance);                
        $instance['is_show_date']        = isset($new_instance['is_show_date']) ? 1 : 0;
        $instance['is_show_comments']    = isset($new_instance['is_show_comments']) ? 1 : 0;
        return $instance;
    }

    function form($instance) {
        parent::form($instance);
        $instance = wp_parse_args((array) $instance, $this->get_default());
        extract($instance);
        ?>
        <p>            
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('is_show_date')); ?>" name="<?php echo esc_attr($this->get_field_name('is_show_date')); ?>" type="checkbox" value="1" <?php checked(1, (int)$is_show_date, true); ?> />            
            <label for="<?php echo esc_attr($this->get_field_id('is_show_date')); ?>"><?php _e('Is show date:', 'origamiez'); ?></label>            
        </p>
        <p>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('is_show_comments')); ?>" name="<?php echo esc_attr($this->get_field_name('is_show_comments')); ?>" type="checkbox" value="1" <?php checked(1, (int)$is_show_comments, true); ?> />            
            <label for="<?php echo esc_attr($this->get_field_id('is_show_comments')); ?>"><?php _e('Is show comments:', 'origamiez'); ?></label>                        
        </p>
        <?php
    }

    protected function get_default() {
        $default = parent::get_default();                    
        $default['is_show_date']        = 1;
        $default['is_show_comments']    = 1;
        return $default;
    } 
}

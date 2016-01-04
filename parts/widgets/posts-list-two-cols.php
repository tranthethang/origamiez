<?php

add_action('widgets_init', array('Origamiez_Widget_Posts_List_Two_Cols', 'register'));

class Origamiez_Widget_Posts_List_Two_Cols extends Origamiez_Posts_Widget {

    public static function register(){
        register_widget('Origamiez_Widget_Posts_List_Two_Cols');
    }

    function __construct() {
        $widget_ops  = array('classname' => 'origamiez-widget-posts-two-cols', 'description' => esc_attr__('Display posts list with layout two cols.', 'origamiez'));
        $control_ops = array('width' => 'auto', 'height' => 'auto');
        parent::__construct('origamiez-widget-posts-two-cols', esc_attr__('Origamiez Posts List Two Cols', 'origamiez'), $widget_ops, $control_ops);
    }

    function widget($args, $instance) {
        extract($args);

        $instance = wp_parse_args((array) $instance, $this->get_default());

        extract($instance);

        $title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);

        echo wp_kses( $before_widget, origamiez_get_allowed_tags() );

        if (!empty($title)){
            echo wp_kses( $before_title . $title . $after_title, origamiez_get_allowed_tags() );
        }
        extract($instance);

        $query = $this->get_query($instance);
        $posts = new WP_Query($query);

        if ($posts->have_posts()):
            ?>
            <div class="row">
                <div class="article-col-left col-sm-6 col-xs-12">
                <?php
                $loop_index = 0;

                while ($posts->have_posts()):
                    $posts->the_post();
                    $post_title = get_the_title();
                    $post_url   = get_permalink();

                    $post_classes = "origamiez-post-{$loop_index} clearfix";

                    if(0 == $loop_index):                                  
                    ?>
                        <article <?php post_class($post_classes); ?>>
                            <?php if(has_post_thumbnail()): ?>
                                <a class="link-hover-effect origamiez-post-thumb" 
                                    href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('origamiez-square-m', 
                                        array('class' => 'image-effect img-responsive')); ?>
                                </a>
                            <?php endif; ?>
                            
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

                            <h3>
                                <a class="entry-title" 
                                    href="<?php echo esc_url($post_url); ?>" 
                                    title="<?php echo esc_attr($post_title); ?>">
                                    <?php echo esc_attr($post_title); ?>
                                </a>
                            </h3>               

                            <?php
                            if($excerpt_words_limit):
                                add_filter('excerpt_length', "origamiez_return_{$excerpt_words_limit}");
                                ?>
                                <p class="entry-excerpt clearfix"><?php echo get_the_excerpt(); ?></p>
                                <?php
                                remove_filter('excerpt_length', "origamiez_return_{$excerpt_words_limit}");
                                endif;
                            ?>                            
                        </article>
                      
                        <?php
                        echo '</div>';
                        echo '<div class="article-col-right col-sm-6 col-xs-12">';
                    else:                                            
                        ?>
                        <article <?php post_class($post_classes); ?>>

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

                            <h5>
                                <a class="entry-title" 
                                    href="<?php echo esc_url($post_url); ?>" 
                                    title="<?php echo esc_attr($post_title); ?>">
                                    <?php echo esc_attr($post_title); ?>
                                </a>
                            </h5>                                       
                        </article>
                        <?php
                    endif;

                    $loop_index++;
                endwhile;
                ?>                    
                </div>
            </div>
            <?php
        endif;
        wp_reset_postdata();
        
        echo wp_kses( $after_widget, origamiez_get_allowed_tags() );
    }

    function update($new_instance, $old_instance) {
        $instance = parent::update($new_instance, $old_instance);        
        $instance['excerpt_words_limit'] = isset($new_instance['excerpt_words_limit']) ? (int) $new_instance['excerpt_words_limit'] : 0;
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
            <label for="<?php echo esc_attr($this->get_field_id('excerpt_words_limit')); ?>"><?php esc_html_e('Excerpt words limit:', 'origamiez'); ?></label>            
            <select class="widefat" 
                id="<?php echo esc_attr($this->get_field_id('excerpt_words_limit')); ?>" 
                name="<?php echo esc_attr($this->get_field_name('excerpt_words_limit')); ?>">
                <?php
                $limits = array(0, 10, 15, 20, 30, 60);
                foreach ($limits as $limit) {
                    ?>
                    <option value="<?php echo esc_attr($limit); ?>" <?php selected($instance['excerpt_words_limit'], $limit); ?>><?php echo esc_attr($limit); ?></option>
                    <?php
                }
                ?>
            </select>            
        </p>
        <p>            
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('is_show_date')); ?>" name="<?php echo esc_attr($this->get_field_name('is_show_date')); ?>" type="checkbox" value="1" <?php checked(1, (int)$is_show_date, true); ?> />            
            <label for="<?php echo esc_attr($this->get_field_id('is_show_date')); ?>"><?php esc_html_e('Is show date:', 'origamiez'); ?></label>            
        </p>
        <p>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('is_show_comments')); ?>" name="<?php echo esc_attr($this->get_field_name('is_show_comments')); ?>" type="checkbox" value="1" <?php checked(1, (int)$is_show_comments, true); ?> />            
            <label for="<?php echo esc_attr($this->get_field_id('is_show_comments')); ?>"><?php esc_html_e('Is show comments:', 'origamiez'); ?></label>                        
        </p>
        <?php
    }
    
    protected function get_default() {
        $default = parent::get_default();            
        $default['excerpt_words_limit'] = 0;
        $default['is_show_date']        = 1;
        $default['is_show_comments']    = 1;

        return $default;
    }   
}

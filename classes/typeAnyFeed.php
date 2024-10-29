<?php
namespace anyfeedretriever;
require_once 'readerAnyFeed.php';
class anyFeedType
{
    function __construct()
    {
        add_action('init', array(
            $this,
            'register_type'
        ));
        add_shortcode('anyfeed', array(
            $this,
            'getScsFeeds'
        ));
        add_shortcode('anyfeed-cat', array(
            $this,
            'getCategoryBlock'
        ));
        add_action('init', array(
            $this,
            'create_usecase_taxonomies'
        ), 0);
        add_action('add_meta_boxes', array(
            $this,
            'create_feed_metabox'
        ));
        add_action('save_post', array(
            $this,
            'save_feed_metabox'
        ));
        apply_filters('post_row_actions', array(
            $this,
            'remove_anyfeedview'
        ), 10, 2);
        add_action("wp_ajax_load_anyfeeds", array(
            $this,
            "loadFeedItems"
        ));
        add_action("wp_ajax_nopriv_load_anyfeeds", array(
            $this,
            "loadFeedItems"
        ));
        add_action("wp_ajax_getfeedimage", array(
            $this,
            "getFeedImage"
        ));
        add_action("wp_ajax_nopriv_getfeedimage", array(
            $this,
            "getFeedImage"
        ));
        add_action('wp_enqueue_scripts', array(
            $this,
            'anyfeed_scripts'
        ));
        add_filter('template_include', array(
            $this,
            'get_anyfeed_template'
        ));
    }
    public function register_type()
    {
        $labels = array(
            'name' => _x('Feeds', 'post type general name'),
            'singular_name' => _x('Feed', 'post type singular name'),
            'add_new' => _x('Add New', 'feed'),
            'add_new_item' => __('Add New Feed '),
            'edit_item' => __('Edit Feed '),
            'new_item' => __('New Feed '),
            'all_items' => __('All Feeds'),
            'view_item' => __('View Feed'),
            'search_items' => __('Search Feed'),
            'not_found' => __('No Feed found'),
            'not_found_in_trash' => __('No Feed found in the Trash'),
            'parent_item_colon' => '',
            'menu_name' => 'Feeds'
        );
        $args   = array(
            'labels' => $labels,
            'description' => '',
            'public' => true,
            'menu_position' => 4,
            'supports' => array(
                'title',
                'thumbnail'
            ),
            'has_archive' => true,
            'rewrite' => array(
                'with_front' => false,
                'slug' => 'anyfeed'
            )
        );
        register_post_type('anyfeed', $args);
    }
    public function getScsFeeds($atts)
    {
        $atts = shortcode_atts(array(
            'show' => '',
            'id' => 0,
            'cat' => '',
            'catslug' => ''
        ), $atts);
        $args = array(
            'post_type' => 'anyfeed',
            'posts_per_page' => 8,
            'p' => (!isset($atts['p']) || $atts['p'] = null) ? $atts['id'] : $atts['p']
        );
        $args = array_merge($atts, $args);
        if (!empty($args['cat'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'custfeedcategory',
                    'field' => 'name',
                    'terms' => explode(',', $args['cat'])
                )
            );
        }
        if (!empty($args['catslug'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'custfeedcategory',
                    'field' => 'slug',
                    'terms' => explode(',', $args['catslug'])
                )
            );
        }
        $wp_query = new \WP_Query($args);
        $output   = '<div class="anyfeed-container">';
        foreach ($wp_query->posts as $post) {
            $post_categories = get_the_term_list($post->ID, 'custfeedcategory');
            $post_tags       = get_the_term_list($post->ID, 'anyfeedgroups');
            $_feedurl        = get_post_meta($post->ID, anyfeedurl, true);
            $_feedblock      = <<<FEEDBLOCK
           <div class="feed-block" data-feedid="{$post->ID}" >
          <!--  <h4>$post->post_title</h4> -->
            <div class="feed-category">{$post_categories}</div>
            <div class="feed-groups">{$post_tags}</div>
            <div class="feed-items" data-feedurl="{$_feedurl}"></div>
</div>
FEEDBLOCK;
            $output .= $_feedblock;
        }
        return $output . '</div>';
    }

    public function getCategoryBlock($atts){
        $atts = shortcode_atts(array(
            'show' => '',
            'id' => 0,
            'cat' => '',
            'catslug' => ''
        ), $atts);
        $output = <<<CATBLOCK
        <!-- anyfeed category block - start -->
        <div class="anyfeed-cta-block"><ul class="anyfeed-categories"></ul></div>
        <!-- anyfeed category block - start -->
CATBLOCK;

        return $output;

    }

    function create_usecase_taxonomies()
    {
        $labels = array(
            'name' => _x('Feed Categories', 'taxonomy general name', 'anyfeed'),
            'singular_name' => _x('Feed Category', 'taxonomy singular name', 'anyfeed'),
            'search_items' => __('Search Feed Categories', 'anyfeed'),
            'all_items' => __('All Feed Categories', 'anyfeed'),
            'parent_item' => __('Parent Feed Category', 'anyfeed'),
            'parent_item_colon' => __('Parent Feed Category:', 'anyfeed'),
            'edit_item' => __('Edit Feed Category', 'anyfeed'),
            'update_item' => __('Update Feed Category', 'anyfeed'),
            'add_new_item' => __('Add New Feed Category', 'anyfeed'),
            'new_item_name' => __('New Feed Category Name', 'anyfeed'),
            'menu_name' => __('Feed Category', 'anyfeed')
        );
        $args   = array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array(
                'slug' => 'cust-feed-category'
            )
        );
        register_taxonomy('custfeedcategory', array(
            'anyfeed'
        ), $args);
        $labels = array(
            'name' => _x('Feed Groups', 'taxonomy general name', 'anyfeed'),
            'singular_name' => _x('Feed Group', 'taxonomy singular name', 'anyfeed'),
            'search_items' => __('Search Feed Groups', 'anyfeed'),
            'popular_items' => __('Popular Feed Groups', 'anyfeed'),
            'all_items' => __('All Feed Groups', 'anyfeed'),
            'parent_item' => null,
            'parent_item_colon' => null,
            'edit_item' => __('Edit Feed Group', 'anyfeed'),
            'update_item' => __('Update Feed Group', 'anyfeed'),
            'add_new_item' => __('Add New Feed Group', 'anyfeed'),
            'new_item_name' => __('New Feed Group', 'anyfeed'),
            'separate_items_with_commas' => __('Separate Feed Group with commas', 'anyfeed'),
            'add_or_remove_items' => __('Add or remove Feed Groups', 'anyfeed'),
            'choose_from_most_used' => __('Choose from the most used Feed Groups', 'anyfeed'),
            'not_found' => __('No Feed Groups found.', 'anyfeed'),
            'menu_name' => __('Feed Groups', 'anyfeed')
        );
        $args   = array(
            'hierarchical' => false,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var' => true,
            'rewrite' => array(
                'slug' => 'feed-groups'
            )
        );
        register_taxonomy('anyfeedgroups', 'anyfeed', $args);
    }
    function create_feed_metabox()
    {
        add_meta_box('anyfeedurl', __('Feed Url', 'anyfeed'), array(
            $this,
            'anyfeedurl_callback'
        ), 'anyfeed');
    }
    function anyfeedurl_callback($post)
    {
        wp_nonce_field(basename(__FILE__), 'anyfeed_fields');
        $_anyfeedurl = get_post_meta($post->ID, 'anyfeedurl', true);
        echo '<input type="text" name="anyfeedurl" value="' . esc_textarea($_anyfeedurl) . '" class="widefat" placeholder="http://example.com/feeds/rss or http://example.com/feeds/atom">';
    }
    function save_feed_metabox($post_id)
    {
        if (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }
        if (!isset($_POST['anyfeedurl']) || !wp_verify_nonce($_POST['anyfeed_fields'], basename(__FILE__))) {
            return $post_id;
        }
        $events_meta['anyfeedurl'] = esc_url($_POST['anyfeedurl']);
        foreach ($events_meta as $key => $value):
            if (get_post_meta($post_id, $key, false)) {
                update_post_meta($post_id, $key, $value);
            } else {
                add_post_meta($post_id, $key, $value);
            }
            if (!$value) {
                delete_post_meta($post_id, $key);
            }
        endforeach;
    }
    function remove_anyfeedview($actions = array())
    {
        echo '<h1>test</h1>';
        unset($actions['inline hide-if-no-js']);
        unset($actions['view']);
        return array();
    }
    public function loadFeedItems()
    {
        if (isset($_POST['feedurl']) && !empty($_POST['feedurl'])) {
            try {
                $_feedurl = esc_url_raw($_POST['feedurl']);
                $options  = array(
                    'url' => $_feedurl
                );

                $reader4  = new AnyFeedReader($options);
                $items    = $reader4->parse()->getData();
                if (!array_key_exists('items', $items)) {
                    $reader4->setOptions(array(
                        'type' => 'rss2'
                    ));
                    $items = $reader4->parse()->getData();
                }
                if (!array_key_exists('items', $items)) {
                    $reader4->setOptions(array(
                        'type' => 'atom'
                    ));
                    $items = $reader4->parse()->getData();
                }
                echo json_encode($items['items']);
            }
            catch (Exception $e) {
                echo json_encode(array(
                    'Message' => $e->getMessage()
                ));
            }
        }
        else{
            echo json_encode(array(
                'Message' => 'Invalid feed url'
            ));
        }
        exit(0);
    }
    public function getFeedImage()
    {
        if (isset($_GET['url']) && !empty($_GET['url'])) {
            include_once 'shmAnyFeed.php';
            $url    = esc_url_raw($_GET['url']);
            $html   = file_get_html($url);
            $result = parse_url($url);
            $domain = $result['scheme'] . "://" . $result['host'];
            $images = $html->find('article img[src^=http]');
            if ($images) {
                foreach ($images as $element) {
                    header('Location: ' . $element->src);
                    exit;
                }
            } else {
                $images = $html->find('img[src^=http][src*=logo]');
                if ($images) {
                    foreach ($images as $element) {
                        header('Location: ' . $element->src);
                        exit;
                    }
                } else {
                    $images = $html->find('img[src*=logo]');
                    if ($images) {
                        foreach ($images as $element) {
                            header('Location: ' . $domain . $element->src);
                            exit;
                        }
                    } else {
                        $images = $html->find('.column--primary figure img[src^=http]');
                        if ($images) {
                            foreach ($images as $element) {
                                header('Location: ' . $element->src);
                                exit;
                            }
                        } else {
                            $images = $html->find('img[itemprop*=logo]');
                            if ($images) {
                                foreach ($images as $element) {
                                    header('Location: ' . $element->src);
                                }
                            } else {
                                header('Location: ' . SCSFEEDDRI . '/assets/images/no-image.png');
                            }
                        }
                    }
                }
            }
        }
        exit;
    }
    function anyfeed_scripts()
    {
        $imageloadurl = "'" . admin_url('admin-ajax.php') . "?action=getfeedimage&url={{link}}'";
        wp_enqueue_script("mustache", SCSFEEDDRI . 'assets/scripts/mustache.js', array(
            'jquery'
        ));
        wp_enqueue_script("underscore", SCSFEEDDRI . 'assets/scripts/underscore-min.js', array(
            'jquery'
        ));
        wp_enqueue_script("anyfeed-scr", SCSFEEDDRI . 'assets/scripts/script.js', array(
            'jquery',
            'mustache',
            'underscore'
        ));
        wp_localize_script('anyfeed-scr', 'anyfeed_var', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'feeditem' => '<div class="feed-item" data-timestamp="{{timestamp}}"> <div class="feed-image" style="background-image: url(' . $imageloadurl . ');"> </div> <div class="feed-content"> <h3 class="feed-title"><a href="{{link}}" class="feed-title">{{title}}</a></h3> <div class="feed-content"> <span class="pub-date">{{date}} ago | From : {{from}}</span> <div class="item-desc">{{description}}</div> </div> <a href="{{link}}" class="feed-more">read more</a> </div> </div>'
        ));
        wp_enqueue_style('anyfeed-style', SCSFEEDDRI . 'assets/style/style.css');
    }
    function get_anyfeed_template($archive_template)
    {
        global $post;
        if ($post->post_type == 'anyfeed') {
            $archive_template = SCSFEEDDIRPATH . '/templates/custfeedcategory.php';
        }
        return $archive_template;
    }
}
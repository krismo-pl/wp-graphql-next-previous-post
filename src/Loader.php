<?php

namespace WPGraphQL\Extensions\NextPreviousPost;

use WPGraphQL\AppContext;
use WPGraphQL\Data\DataSource;
use WPGraphQL\Model\Post;

/**
 * Class Loader
 *
 * This class allows you to see the next and previous posts in the 'post' type.
 *
 * @package WNextPreviousPost
 * @since   0.1.0
 */
class Loader
{
    public static function init()
    {
        define('WP_GRAPHQL_NEXT_PREVIOUS_POST', 'initialized');
        (new Loader())->bind_hooks();
    }

    public function bind_hooks()
    {
        add_action(
            'graphql_register_types',
            [$this, 'npp_action_register_types'],
            9,
            0
        );

    }

    public function npp_action_register_types()
    {
        register_graphql_field('Post', 'next', [
            'type' => 'Post',
            'description' => __(
                'Next post'
            ),
            'resolve' => function (Post $post, array $args, AppContext $context) {
                global $post;

                // get post
                $post = get_post($post->ID, OBJECT);

                // setup global $post variable
                setup_postdata($post);

                //get all the tems of language taxonomy 
                $terms = get_terms( 'language', array(
                    'hide_empty' => false,
                ) );
                $excludeds_term_ids = array();
                foreach($terms as $term){
                    if($term->slug !='en'){   // exlude all of them other than 'en'
                        $excludeds_term_ids[]= $term->term_id; 
                    }
                }
                
                //$next = get_next_post();
                $next = get_adjacent_post(true,$excludeds_term_ids,false,'language');
                

                wp_reset_postdata();

                if (!$next) {
                    return null;
                }

                return DataSource::resolve_post_object($next->ID, $context);
            },
        ]);

        register_graphql_field('Post', 'previous', [
            'type' => 'Post',
            'description' => __(
                'Previous post'
            ),

            'resolve' => function (Post $post, array $args, AppContext $context) {
                global $post;

                // get post
                $post = get_post($post->ID, OBJECT);

                // setup global $post variable
                setup_postdata($post);
                
                //get all the tems of language taxonomy 
                $terms = get_terms( 'language', array(
                    'hide_empty' => false,
                ) );
                $excludeds_term_ids = array();
                foreach($terms as $term){
                    if($term->slug !='en'){   // exlude all of them other than 'en'
                        $excludeds_term_ids[]= $term->term_id; 
                    }
                }
                
                //$prev = get_previous_post();
                $prev = get_adjacent_post(true,$excludeds_term_ids,false,'language');

                wp_reset_postdata();

                if (!$prev) {
                    return null;
                }

                return DataSource::resolve_post_object($prev->ID, $context);
            },
        ]);
    }
}

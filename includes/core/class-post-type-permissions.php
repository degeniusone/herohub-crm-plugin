<?php
namespace HeroHub\CRM\Core;

/**
 * Class Post_Type_Permissions
 * Handles role-based access control for custom post types
 */
class Post_Type_Permissions {
    /**
     * @var Roles_Manager
     */
    private $roles_manager;

    /**
     * Initialize the post type permissions
     */
    public function __construct() {
        $this->roles_manager = new Roles_Manager();
        
        // Filter post queries
        add_filter('pre_get_posts', array($this, 'filter_posts_by_role'));
        
        // Filter post row actions
        add_filter('post_row_actions', array($this, 'filter_row_actions'), 10, 2);
        add_filter('page_row_actions', array($this, 'filter_row_actions'), 10, 2);
        
        // Check edit/delete permissions
        add_filter('user_has_cap', array($this, 'check_post_permissions'), 10, 4);
        
        // Filter REST API responses
        add_filter('rest_prepare_post', array($this, 'filter_rest_response'), 10, 3);
    }

    /**
     * Filter posts based on user role
     * 
     * @param \WP_Query $query The WordPress query object
     * @return \WP_Query Modified query object
     */
    public function filter_posts_by_role($query) {
        if (is_admin() && !defined('DOING_AJAX') && $query->is_main_query()) {
            $post_types = array('contact', 'deal', 'property', 'event', 'activity');
            
            if (in_array($query->get('post_type'), $post_types)) {
                $user_id = get_current_user_id();
                
                // Admins and managers can see all posts
                if ($this->roles_manager->is_manager($user_id)) {
                    return $query;
                }
                
                // Agents can only see their own posts
                if ($this->roles_manager->is_agent($user_id)) {
                    $query->set('meta_key', '_assigned_agent');
                    $query->set('meta_value', $user_id);
                }
            }
        }
        
        return $query;
    }

    /**
     * Filter row actions based on user role and capabilities
     * 
     * @param array $actions Array of action links
     * @param \WP_Post $post The post object
     * @return array Modified array of action links
     */
    public function filter_row_actions($actions, $post) {
        $post_types = array('contact', 'deal', 'property', 'event', 'activity');
        
        if (in_array($post->post_type, $post_types)) {
            $user_id = get_current_user_id();
            
            // Remove edit/delete actions if user doesn't have permission
            if (!$this->roles_manager->user_can_for_post('edit_own_' . $post->post_type . 's', $post->ID, $user_id)) {
                unset($actions['edit']);
                unset($actions['inline hide-if-no-js']);
            }
            
            if (!$this->roles_manager->user_can_for_post('delete_own_' . $post->post_type . 's', $post->ID, $user_id)) {
                unset($actions['trash']);
                unset($actions['delete']);
            }
        }
        
        return $actions;
    }

    /**
     * Check post permissions based on user role and capabilities
     * 
     * @param array $allcaps Array of all capabilities
     * @param array $caps Required capabilities
     * @param array $args Additional arguments
     * @param \WP_User $user User object
     * @return array Modified capabilities array
     */
    public function check_post_permissions($allcaps, $caps, $args, $user) {
        // Only check for our custom post types
        if (!isset($args[2])) {
            return $allcaps;
        }

        $post = get_post($args[2]);
        if (!$post) {
            return $allcaps;
        }

        $post_types = array('contact', 'deal', 'property', 'event', 'activity');
        if (!in_array($post->post_type, $post_types)) {
            return $allcaps;
        }

        // Map WordPress capabilities to our custom capabilities
        $cap_map = array(
            'edit_post' => 'edit_own_',
            'delete_post' => 'delete_own_',
            'read_post' => 'view_own_'
        );

        foreach ($caps as $cap) {
            if (isset($cap_map[$cap])) {
                $custom_cap = $cap_map[$cap] . $post->post_type . 's';
                if (!$this->roles_manager->user_can_for_post($custom_cap, $post->ID, $user->ID)) {
                    $allcaps[$cap] = false;
                }
            }
        }

        return $allcaps;
    }

    /**
     * Filter REST API responses based on user role
     * 
     * @param \WP_REST_Response $response The response object
     * @param \WP_Post $post The post object
     * @param \WP_REST_Request $request The request object
     * @return \WP_REST_Response Modified response
     */
    public function filter_rest_response($response, $post, $request) {
        $post_types = array('contact', 'deal', 'property', 'event', 'activity');
        
        if (in_array($post->post_type, $post_types)) {
            $user_id = get_current_user_id();
            
            if (!$this->roles_manager->user_can_for_post('view_own_' . $post->post_type . 's', $post->ID, $user_id)) {
                return new \WP_Error(
                    'rest_forbidden',
                    __('You do not have permission to view this item.', 'herohub-crm'),
                    array('status' => 403)
                );
            }
        }
        
        return $response;
    }
}

<?php
namespace KAMAL\NFC\Admin;

class NFC_Tag_Widget extends \WP_Widget {
    public function __construct() {
        $widget_options = [
            'classname'   => 'nfc_tag_widget',
            'description' => __( 'NFC Tag Widget to display post category with follow button', 'nfc' ),
        ];
        parent::__construct( 'nfc_tag_widget', __( 'NFC Tag Widget', 'nfc' ), $widget_options );

        add_action( 'wp_ajax_nfc_tag_ajax_get_id', [$this, 'nfc_ajax_get_tags_id'] );
        add_action( 'wp_ajax_nopriv_nfc_tag_ajax_get_id', [$this, 'nfc_ajax_get_tags_id_no'] );
        add_action( 'pre_get_posts', [$this, 'set_posts_tags'] );
    }

    /**
     * Set Posts Query
     *
     * @param $query
     */
    public function set_posts_tags( $q ) {
        if ( is_user_logged_in() ) { // First check if we have a logged in user before doing anything
            if ( $q->is_home() // Only targets the main page, home page
                 && $q->is_main_query() // Only targets the main query
            ) {
                if ( is_active_widget( false, false, 'nfc_tag_widget', true ) ) {
                    // Get the current logged in user obejct
                    $current_logged_in_user = wp_get_current_user();

                    $term = get_user_meta( $current_logged_in_user->ID, 'post_tag_ids', true );

                    if ( $term ) {
                        $q->set( 'tag__in', $term );
                    }
                }
            }
        }

    }

    /**
     * Save, Remove category ID in user meta.
     *
     * @return array
     * @return string
     * @return JSON
     */
    public function nfc_ajax_get_tags_id() {
        global $wpdb;

        if ( !isset( $_POST ) || empty( $_POST ) || !is_user_logged_in() ) {
            echo __( 'Oops! Try again.', 'nfc' );
            exit;
        }
        $current_user = wp_get_current_user();
        $saved_ids    = get_user_meta( $current_user->ID, 'post_tag_ids', true );

        $id_to_add = isset( $_POST["data"] ) ? intval( $_POST["data"] ) : '';

        if ( in_array( $id_to_add, $saved_ids ) ) {

            if (  ( $key = array_search( $id_to_add, $saved_ids ) ) !== false ) {
                unset( $saved_ids[$key] );
                update_user_meta( $current_user->ID, 'post_tag_ids', $saved_ids );
                return wp_send_json( ['value' => __( 'unfollowed', 'nfc' )] );
            }

        } else {
            //if list is empty, initialize it as an empty array
            if ( $saved_ids == '' ) {
                $saved_ids = array();
            }

            // push the new ID inside the array
            array_push( $saved_ids, $id_to_add );

            if ( update_user_meta( $current_user->ID, 'post_tag_ids', $saved_ids ) ) {
                return wp_send_json( ['value' => __( 'followed', 'nfc' )] );
            } else {
                echo __( 'Failed: Could not update user meta.', 'nfc' );
            }
        }

        die();

    }

    /**
     * No logged in user message
     *
     * @return string
     */
    public function nfc_ajax_get_tags_id_no() {
        echo __( "you're not logged in, please login first.", 'nfc' );
    }

    /**
     * Register NFC Category Widget
     *
     * @return void
     */
    public function nfc_tag_widget_reg() {
        register_widget( $this );
    }
    /**
     * Display Widget
     *
     * @param $args
     * @param $instance
     */
    public function widget( $args, $instance ) {

        echo $args['before_widget'];
        if ( !empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        }
        $current_logged_in_user = wp_get_current_user();
        $user_followed_ids      = get_user_meta( $current_logged_in_user->ID, 'post_tag_ids', true );

        $followed_tags = get_terms( array(
            'taxonomy'   => 'post_tag',
            'hide_empty' => false,
            'include'    => $user_followed_ids,
        ) );

        $all_tags = get_terms( array(
            'taxonomy'   => 'post_tag',
            'hide_empty' => false,
            //'exclude'    => $user_followed_ids,
        ) );

?>
<div id="test2" style="display: none;"></div>
 <?php
        if (is_user_logged_in()) :
        
            if (!empty($user_followed_ids)) :
                $tag_fav = "";
                foreach ($followed_tags as $followed_tag) :
                 $tag_fav = $tag_fav.'<span data-key="'.esc_attr($followed_tag->term_id).'" data-display="'
                 .esc_html($followed_tag->name).'" class="tag label label-info valid">'.esc_html($followed_tag->name).
                 '<span data-role="remove" title="Remove"></span>
                    </span>'; 
                endforeach;
         ?>       
                <input type="hidden" id="favo_tags" name="favo_tags" value="<?php print_r(esc_html($tag_fav)); ?>">
        <?php
            endif;
        endif;

 
        $tag_json  = "";
        if (!empty($all_tags)) :
            $i102 = 1;
            $tag_json = "[";
            foreach ($all_tags as $single_tag) :
                 
                if ($i102 != count($all_tags)) {
                    $tag_json = $tag_json . '{ "key": ' . esc_attr($single_tag->term_id) . ', "value": "' . esc_html($single_tag->name) . '" },';
                } else {
                    $tag_json = $tag_json . '{ "key": ' . esc_attr($single_tag->term_id) . ', "value": "' . esc_html($single_tag->name) . '" }';
                }
             
                $i102++;
            endforeach;
            $tag_json = $tag_json . "]";

        ?>
            <input type="hidden" id="tag_list" name="tag_list" value="<?php print_r(esc_html($tag_json)); ?>">
        <?php
        endif;

        //----------------------------------------------------------------------------

        echo $args['after_widget'];
    }

    /**
     * Widget form
     *
     * @param  mixed  $instance
     * @return void
     */
    public function form( $instance ) {
        $title = !empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Follow Tag', 'nfc' );
        ?>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'nfc' );?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
}

    /**
     * update widget form instance
     *
     * @param  mixed  $new_instance
     * @param  mixed  $old_instance
     * @return void
     */
    public function update( $new_instance, $old_instance ) {
        $instance          = array();
        $instance['title'] = ( !empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';

        return $instance;
    }
}
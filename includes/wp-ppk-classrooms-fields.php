<?php

function ppk_add_metabox_for_classroom() {
    add_meta_box(
            'classroom_attribute_metabox', 'Classroom Information', 'ppk_classroom_meta_box_content', 'ppk_classroom', 'normal', 'high'
    );
}

add_action('add_meta_boxes', 'ppk_add_metabox_for_classroom');

function ppk_classroom_meta_box_content($post) {

    wp_nonce_field(basename(__FILE__), 'wp_ppk_classrooms_nonce');
    
    $classroom_floor = get_post_meta($post->ID, '_classroom_floor', true);
    $classroom_capacity = get_post_meta($post->ID, '_classroom_capacity', true);
    ?>
    <table >
        <tbody>
            <tr>
                <th scope="row">Classroom floor:</th>
                <td>
                    <input type="text" name="classroom_metabox_floor" id="classroom_metabox_floor" value="<?php if (isset($classroom_floor)) {
        echo esc_attr($classroom_floor);
    } ?>" style="width:300px;" />
                </td>
            </tr>
            <tr>
                <th scope="row">Classroom capacity:</th>
                <td>
                    <input type="text" name="classroom_metabox_capacity" id="classroom_metabox_capacity" value="<?php if (isset($classroom_capacity)) {
        echo esc_attr($classroom_capacity);
    } ?>" style="width:300px;" />
                </td>
            </tr>
        </tbody>
    </table>
    <?php
}

function ppk_save_classroom_metabox($post_id) {
    
    $is_autosave = wp_is_post_autosave($post_id);
    $is_revision = wp_is_post_revision($post_id);
    $is_valid_nonce = (isset($_POST['wp_ppk_classrooms_nonce']) && wp_verify_nonce($_POST['wp_ppk_classrooms_nonce'], basename(__FILE__))) ;
    
    if($is_autosave || $is_revision || $is_valid_nonce)
    {
        return;
    }
    
    if ($_POST) {
        $classroom_floor = 0;
        if (isset($_POST['classroom_metabox_floor'])) {
            $classroom_floor = sanitize_text_field($_POST['classroom_metabox_floor']);
        }
        $classroom_capacity = 0;
        if (isset($_POST['classroom_metabox_capacity'])) {
            $classroom_capacity = sanitize_text_field($_POST['classroom_metabox_capacity']);
        }

        update_post_meta($post_id, '_classroom_floor', $classroom_floor);
        update_post_meta($post_id, '_classroom_capacity', $classroom_capacity);
    }
}

add_action('save_post', 'ppk_save_classroom_metabox');

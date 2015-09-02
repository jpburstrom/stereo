<?php 
/**
 * Stereo
 * Johannes Burström 2013
 *
 * Attachment upload filters
 */


class StereoAttachment {
    
    private $id3data = array();
    private $fields = array(
        "artist" => "text", 
        "album" => "text", 
        "track_number" => "int", 
        "year" => "int", 
        "genre" => "text"
    );

    function __construct() {
        // attach our function to the correct hook
        add_filter("attachment_fields_to_edit", array(&$this, "edit_fields"), null, 2);
        add_filter("attachment_fields_to_save", array(&$this, "save_fields"), null, 2);
        add_filter( 'wp_generate_attachment_metadata', array(&$this, "generate_metadata"), 10, 2);
        add_action('delete_attachment', array(&$this, 'delete_attachment'));
    }

    /**
     * Adding our custom fields to the $form_fields array
     *
     * @param array $form_fields
     * @param object $post
     * @return array
     */
    function edit_fields($form_fields, $post) {

        if (strpos($post->post_mime_type, "audio") !== 0) return $form_fields;
        $attachment_data = get_post_meta($post->ID, "_stereo_metadata", true);

        foreach ($this->fields as $key => $value) {
            $form_fields["stereo_$key"]["label"] = ucwords(str_replace("_", " ", $key));
            $form_fields["stereo_$key"]["input"] = "text";
            $form_fields["stereo_$key"]["value"] = (isset($attachment_data[$key])) ? $attachment_data[$key] : "";
        }

        return $form_fields;
    }

    /**
     * @param array $post
     * @param array $attachment
     * @return array
     */
    function save_fields($post, $attachment) {

        $data = get_post_meta($post->ID, "_stereo_metadata", true);
        foreach ($this->fields as $field => $type) {
            $update = true;
            if( isset($attachment["stereo_" . $field]) ){
                $formfield = "stereo_" . $field;
                if ($type == "int" && (!empty($attachment[$formfield]) && !is_numeric($attachment[$formfield]))) {
                        $update = false;
                        $post['errors'][$formfield]['helps'][] = __('This should be a number.');
                }
                if ($update)
                    $data[$field] = $attachment[$formfield];
            }
        }
        if ($data) update_post_meta($post['ID'], "_stereo_metadata", $data);
        return $post;
    }


    function generate_metadata($metadata, $id=false) 
    {
        if ( ! preg_match('!^audio/!', get_post_mime_type( $id )))
            return $metadata;

        $data = wp_read_audio_metadata(get_attached_file($id));

        if ( trim( $data['title'] ) ) {
            $attachment = array();
            $attachment['ID'] = $id;
            $attachment['post_title'] = $data['title'];
            wp_update_post( $attachment );
        }

        unset ($data['title']);
        update_post_meta($id, "_stereo_metadata", $data);

        return $metadata;

    }

    function get_metadata($id) {
        return get_post_meta($id, "_stereo_metadata", true);
    }

    //See stereo_custom_post:321
    function delete_attachment($id) {
        $tracks = get_posts( array(
            'post_type' => 'stereo_track',
            'nopaging' => true,
            'meta_key' => '_stereo_wp_fileid',
            'meta_value' => $id
        ));
        foreach ($tracks as $track) {
            $m = get_stereo_track_meta($track->ID);
            unset($m['host'], $m['fileid']);
            update_post_meta($track->ID, '_stereo', $m);
            delete_post_meta($id, '_stereo_wp_fileid');

        }

    }
}

$stereo_attachment = new StereoAttachment();


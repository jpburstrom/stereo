<?php 
/**
 * Stereo
 * Johannes Burström 2013
 *
 * Attachment upload filters
 */


if (!class_exists("getID3")) {
    require(STEREO_PLUGIN_DIR . "admin/lib/getid3/getid3.php");
}

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
            $form_fields["stereo_$key"]["value"] = $attachment_data[$key];
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

        $data = $this->_get_id3_data(get_attached_file($id));

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

    private function _get_id3_data($file) {
        if ($data = $this->id3data[$file]) {
            return $data;
        }

        $getID3 = new getID3;
        $id3data = $getID3->analyze($file);
        getid3_lib::CopyTagsToComments($id3data);

        $data = array();
        if ($id3data["comments_html"]) {
            foreach ($this->fields as $field => $type) {
                $val = $id3data["comments_html"][$field];
                if ($val) {
                    $val = implode(",", $val);
                    $data[$field] = $val;
                }
            }
        }

        $this->id3data[$file] = $data;
        return $data;

    }

    function get_metadata($id) {
        return get_post_meta($id, "_stereo_metadata", true);
    }
}

$stereo_attachment = new StereoAttachment();


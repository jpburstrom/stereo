<?php 
/*
 * Plugin name: Stereo
 * Description: A Blank Slate
 * Author: Johannes Burström
 */

if (!class_exists("getID3")) {
    include_once("getid3/getid3.php");
}

class StereoAttachment {
    
    private $fields = array(
        "title" => "text", 
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
        add_filter( 'wp_generate_attachment_metadata', "generate_metadata", 10, 2);
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
        $attachment_data = get_post_meta($post->ID, "_jb_audio_metadata", true);

        foreach ($this->fields as $key => $value) {
            $form_fields["jb_audio_$key"]["label"] = __("♪ " .ucwords(str_replace("_", " ", $key)));
            $form_fields["jb_audio_$key"]["input"] = "text";
            $form_fields["jb_audio_$key"]["value"] = $attachment_data[$key];
        }
        $form_fields["jb_audio_title"]["label"] = "♪ Song Title";
        $form_fields["jb_audio_title"]["helps"] = "Use this title for Stereo";

        return $form_fields;
    }

    /**
     * @param array $post
     * @param array $attachment
     * @return array
     */
    function save_fields($post, $attachment) {

        $data = get_post_meta($post->ID, "_jb_audio_metadata", true);
        foreach ($this->fields as $field => $type) {
            $update = true;
            if( isset($attachment["jb_audio_" . $field]) ){
                $formfield = "jb_audio_" . $field;
                if ($type == "int" && (!empty($attachment[$formfield]) && !is_numeric($attachment[$formfield]))) {
                        $update = false;
                        $post['errors'][$formfield]['helps'][] = __('This should be a number.');
                }
                if ($update)
                    $data[$field] = $attachment[$formfield];
            }
        }
        if ($data) update_post_meta($post['ID'], "_jb_audio_metadata", $data);
        return $post;
    }


    function generate_metadata($metadata, $id=false) 
    {
        $file = get_attached_file($id);

        //Do stuff with file
        //
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

        update_post_meta($id, "_jb_audio_metadata", $data);

        return $metadata;

    }


}

$stereo_attachment = new StereoAttachment();


<?php

/**
 * Uploads image given as a binnary stream or the base64 data.
 */
function imgevr_upload_image(){

    $mime = !empty( $_POST['imgMime'] ) ? $_POST['imgMime'] : null;
    $name = !empty( $_POST['imgName'] ) ? $_POST['imgName'] : null;
    $parentId = isset( $_POST['imgParent'] ) ? intval($_POST['imgParent']) : 0;
    $ref = isset( $_POST['imgRef'] ) ? $_POST['imgRef'] : false;    

    if ( empty($mime) ) {
        if ( !empty( $_POST['file'] ) && preg_match('/image\/[a-z0-9]+/', $_POST['file'], $matches) ) {
            $mime = $matches[0];
        } else {
            factory_json_error('Unable to get mime type of the file.');
        }
    }

    // gets extension
    $parts = explode('/', $mime);
    $ext = empty( $parts[1] ) ? 'png' : $parts[1];

    // check the path to upload
    $uploadInfo = wp_upload_dir();
    $targetPath = $uploadInfo['path'];
    if ( !is_dir($targetPath) ) mdir($targetPath, 0777, true);

    // move the uploaded file to the upload path
    $imageName = ( !empty($name) && $name !== 'undefined' ) 
                    ? factory_filename_without_ext($name) 
                    : 'img_' . uniqid();
    
    $target = $targetPath . '/' . $imageName . '.' . $ext;
    
    if ( isset( $_FILES['file'] ) ) {

        if ( empty( $_FILES['file']['size'] ) ) {
            factory_json_error('Sorry, the error of reading image data occured. May be the image is empty of has incorrect format.');
        }
        
        $source = $_FILES['file']['tmp_name'];
        move_uploaded_file($source, $target);
    } else {
        if ( preg_match('/base64,(.*)/', $_POST['file'], $matches) ) {
            $img = str_replace(' ', '+', $matches[1]);
            $data = base64_decode($img);
            $success = file_put_contents($target, $data);

            if ( !$success ) factory_json_error('Unable to save the image.');
        } else {
            factory_json_error('Incorrect file format (base64).');
        }
    }
    
    $attachment = array(
        'guid' => $uploadInfo['url'] . '/' . $imageName . '.' . $ext,
        'post_mime_type' => $mime,
        'post_title' => $imageName,
        'post_name' => $imageName,
        'post_content' => '',
        'post_status' => 'inherit',
    );

    $id = wp_insert_attachment( $attachment, $target, $parentId );

    // for the function wp_generate_attachment_metadata() to work
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $attach_data = wp_generate_attachment_metadata( $id, $target );
    wp_update_attachment_metadata( $id, $attach_data );

    $result = array(
        'url' => $uploadInfo['url'] . '/' . $imageName . '.' . $ext,
        'id' => $id
    );
    
    echo json_encode($result);
    exit;
}

add_action('wp_ajax_imageinsert_upload', 'imgevr_upload_image');

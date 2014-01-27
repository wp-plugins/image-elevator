<?php
#build: premium, offline

/**
 * Renders the form.
 */
function imgevr_settings() {

    $form = new FactoryForms300_Form(array(
        'scope' => 'imgelv'
    ));

    $form->controlTheme = 'mendeleev-300';
    $form->setProvider( new FactoryForms300_OptionsValueProvider(array(
        'scope' => 'imgelv'
    )));
    
    $form->add(array(

        array(
            'type'      => 'checkbox',
            'name'      => 'clipboard_enable',
            'title'     => __( 'Copy & Paste', 'imgelv' ),
            'default'   => true,
            'hint'      => 'If on, enable to paste images from clipboard directly into the post editor.'
        ),
        array(
            'type'      => 'checkbox',
            'name'      => 'dragdrop_enable',
            'title'     => __( 'Drag & Drop', 'imgelv' ),
            'default'   => true,
            'hint'      => 'If on, enable to add local images by dragging and dropping.'
        ),
        array(
            'type' => 'separator'
        ),
        array(
            'type'      => 'checkbox',
            'name'      => 'compression_enable',
            'title'     => __('Clipboard Compression', 'imagelv'),
            'hint'      => 'if on, images pasted from the clipboard and having the size more then allowed will be converted to jpeg.',
            'default'   => false
        )
    ));
    
    if ( !function_exists('imagejpeg') ) {
        $form->add(array(
            array(
                'type' => 'div',
                'id'   => 'compression-options-wrap',
                'items' => array(
                    array(
                        'type'      => 'html',
                        'html'      => '<div class="alert" style="max-width: 600px;">GD library is not available on your server. Please ask your host provider to enable GD library.</div>'
                    )
                )
            ) 
        ));
    } else {
        $form->add(array(
            array(
                'type' => 'div',
                'id'   => 'compression-options-wrap',
                'items' => array(
                    array(
                        'type'      => 'textbox',
                        'name'      => 'compression_max_size',
                        'title'     => __( 'Allowed Max Size', 'imgelv' ),
                        'default'   => 400,
                        'units'     => 'Kb',
                        'hint'      => 'The max allowed size of an image pasted from clipboard in Kb. If the image is greater, it will be compressed.'
                    ),
                    array(
                        'type'      => 'textbox',
                        'name'      => 'compression_quality',
                        'title'     => __( 'JPEG Quality', 'imgelv' ),
                        'default'   => 80,
                        'units'     => '%',
                        'hint'      => 'JPEG quality for converting an image from clipboard (0%-100%).'
                    ),
                )
            ) 
        ));
    }

    if ( isset( $_POST['save-action'] ) ) {
        $maxSize = intval( $_POST['imgelv_compression_max_size'] );
        if ( $maxSize <= 0 ) $maxSize = 400;
        $_POST['imgelv_compression_max_size'] = $maxSize;

        $quality = intval( $_POST['imgelv_compression_quality'] );
        if ( $quality <= 0 || $quality > 100 ) $quality = 80;
        $_POST['imgelv_compression_quality'] = $quality;
    
        $form->save();
    }
?>
<script>
    (function($){
        $(function(){
            var compressinCheckbox = $("#imgelv_compression_enable");
            
            compressinCheckbox.change(function(){
                if ( compressinCheckbox.is(':checked') ) {
                    $("#compression-options-wrap").fadeIn(200);
                } else {
                    $("#compression-options-wrap").fadeOut(200);    
                }
            });  
            compressinCheckbox.change();
        });
    })(jQuery);
</script>

<style>
    .factory-control-compression_max_size .col-sm-10 .input-group,
    .factory-control-compression_quality .col-sm-10 .input-group {
        width: 100px;
    }
</style>

<div class="wrap">
    <h2>Image Elevator Settings</h2>
    <p style="margin-top: 0px;">Set here image parameters that are added via Copy & Paste and Drag & Drop.</p>
    
    <div class="factory-bootstrap-300" id="imgelv-settings">
    <form method="post" class="form-horizontal">
    
        <?php if ( isset( $_POST['save-action'] ) ) { ?>
        <div id="message" class="alert alert-success">
            <p>The settings have been updated successfully!</p>
        </div>
        <?php } ?>

        <div style="padding-top: 10px;">
            <?php $form->html(); ?>
        </div>
        
        <div class="form-group form-horizontal">
            <label class="col-sm-2 control-label"> </label>
            <div class="control-group controls col-sm-10">
            <input name="save-action" class="btn btn-primary" type="submit" value="Save changes"/>
            </div>
        </div>

    </form>
    </div>
</div>
<?php
}
<?php

add_filter('factory_admin_notices-clipboard-images', 'imgevr_admin_notices', 10, 2);

function imgevr_admin_notices( $notices, $plugin ) {
    if ( !$plugin->license || $plugin->build !== "free" ) return $notices;
    $closed = get_option('fy_closed_notices', array());
    if ( get_option('fy_trial_activated_' . $plugin->pluginName, false) ) return $notices;
    
    // offer to try premium version after installation, it's shown once
    if ( !isset( $closed['imgevr-trial-1'] ) ) {
        
        $notices[] = array(
            'id'        => 'imgevr-trial-1',
            'class'     => 'image-elevator',

            // content and color
            'type'      => 'offer',
            'header'    => 'Do not Miss it',
            'message'   => '- Try a premium version of <a target="_blank" href="' . $plugin->options['premium'] . '" class="highlighted">Image Elevator</a> 
                            for 7-days trial period, get more features!
                            Rename and compress images on the fly, drag & drop local files! Just click to activate! 
                            <a target="_blank" href="' . $plugin->options['premium'] . '">Learn more</a>.',

            // buttons and links
            'buttons'   => array(
                array(
                    'title'     => 'No, thanks',
                    'action'    => 'x'
                ),  
                array(
                    'title'     => 'Yes, activate it now!',
                    'class'     => 'primary',
                    'action'    => onepress_get_link_license_manager('clipboard-images', 'activateTrial')
                )
            )
        );
    }
    
    // offer to try premium version after using the plugin during 1 day
    if ( isset( $closed['imgevr-trial-1'] ) && !isset( $closed['imgevr-trial-2'] ) ) {

        $time = $closed['imgevr-trial-1']['time'];

        if ( $time + 60*60*24 <= time() ) {
            
            $notices[] = array(
                'id'        => 'imgevr-trial-2',
                'class'     => 'image-elevator',

                // cotnent and color
                'type'      => 'offer',
                'header'    => 'Thank you!',
                'message'   => 'You use <a target="_blank" href="' . $plugin->options['premium'] . '" class="highlighted">Image Elevator</a> already during a day. All right?
                                May be would you like to get more features? Check out a <a target="_blank" href="' . $plugin->options['premium'] . '">premium version</a>.',

                // buttons and links
                'buttons'   => array(
                    array(
                        'title'     => 'No, thanks',
                        'action'    => 'x'
                    ),  
                    array(
                        'title'     => 'Activate trial for 7 days',
                        'class'     => 'primary',
                        'action'    => onepress_get_link_license_manager('clipboard-images', 'activateTrial')
                    )
                )
            );
        }
    }
    
    // just remember about the premium version every week
    if ( isset( $closed['imgevr-trial-2'] ) ) {
        $time = $closed['imgevr-trial-2']['time'];
        $never = false;
        
        if ( isset( $closed['imgevr-trial-3'] ) ) {
            $time = $closed['imgevr-trial-3']['time'];
            $never = $closed['imgevr-trial-3']['never'];
        }

        if ( !$never && ( $time + (60*60*24*5) <= time() ) ) {
            
            $notices[] = array(
                'id'        => 'imgevr-trial-3',
                'class'     => 'image-elevator',

                // content and color
                'type'      => 'offer',
                'header'    => 'Do you remember...',
                'message'   => 'that there\'s a premium version of <a target="_blank" href="' . $plugin->options['premium'] . '" class="highlighted">Image Elevator</a> plugin?
                                Rename and compress images on the fly, drag & drop local files! <a target="_blank" href="' . $plugin->options['premium'] . '">Learn more</a>.',   

                // buttons and links
                'buttons'   => array(
                    array(
                        'title'     => 'Remind later',
                        'action'    => 'x'
                    ), 
                    array(
                        'title'     => 'Never show it',
                        'action'    => 'xx'
                    ),  
                    array(
                        'title'     => 'Activate trial now!',
                        'class'     => 'primary',
                        'action'    => onepress_get_link_license_manager('clipboard-images', 'activateTrial')
                    )
                )
            );
        }
    }
    
    return $notices;
}
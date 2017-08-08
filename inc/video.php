<?php

class PostFormatVideo{

    public $screens = array();

    public function __construct($screens = array())
    {
        $this->screens = $screens;
    }

    public function register_video($screens = array()){

        foreach($this->screens as $screen){
            add_meta_box(
                'post_formats_video',
                __('Video', 'post-formats'),
                array($this, 'video_meta_box'),
                $screen,
                'normal',
                'default'
            );
        }
    }

    public function enqueue_scripts()
    {

    }

    /**
    * [meta_box description]
    * @param  [type] $post [description]
    * @return [type]       [description]
    */
    public function video_meta_box($post){


        wp_nonce_field('post_format_video_nonce', 'post_format_video_nonce');
        $url = get_post_meta($post->ID, '_post_format_video', true);
        $type = $this->get_video_type($url);
        ?>

        <input type="hidden" id="post_format_video" name="post_format_video" value="<?php echo($url); ?>" />

        <div class="pfp-media-holder">
            <?php #echo $this->get_the_video_markup($url); ?>
            <video class="<?php echo $this->displayVideo($url); ?> pfp-video" src="<?php echo(esc_url($url)); ?>" controls="controls"></video>
            <iframe class="<?php echo $this->displayVideo($url, 'youtube'); ?> pfp-embed" src="<?php echo esc_url( $this->youtubeEmbedLink($url)) ; ?>" frameborder="0"></iframe>
        </div>

        <a class="button" data-filter="video" id="post_format_video_select">
            <span class="dashicons dashicons-video-alt3"></span>
            Select Video
        </a>

        <span class="pfp-or-hr">or use YouTube URL:</span>
        <input type="url" id="post_format_video_url" value="<?php echo $type == 'youtube' ? $url : ''; ?>" />
        <a class="js--pfp-remove-video" href="#" >Remove Video</a>

        <?php
    }


    private function displayVideo($url = null, $type = 'local')
    {
        $vis = 'pfp-hide';
        if(!$url)
            return $vis;

        if($type == 'youtube' && $this->get_video_type($url) == 'youtube')
            $vis = '';

        if($type !=='youtube' && in_array( $this->get_video_type($url), array('mp4','mov','webm') ) )
            $vis = '';

        return $vis;
    }

    public function video_meta_box_save($post_id){
        $is_autosave = wp_is_post_autosave($post_id);
        $is_revision = wp_is_post_revision($post_id);
        $is_valid_nonce = (isset($_POST[ 'post_format_video_nonce' ]) && wp_verify_nonce($_POST['post_format_video_nonce'], 'post_format_video_nonce')) ? 'true' : 'false';

        if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
            return;
        }

        if(isset($_POST['post_format_video'])){
            update_post_meta($post_id, '_post_format_video', $_POST['post_format_video']);
        }
    }




    private function get_video_type($url) {


        $type = null;
        if('.mp4' == substr( $url, -4 ) ){
            $type = 'mp4';
        } elseif( '.mov' == substr( $url, -4 ) ) {
            $type = 'mov';
        } elseif('.webm' == substr( $url, -5 )) {
            $type = 'webm';
        } elseif ( preg_match( '#^https?://(?:www\.)?(?:youtube\.com/watch|youtu\.be/)#', $url ) ) {
            $type = 'youtube';
        } elseif( preg_match('#^https?://(.+\.)?vimeo\.com/.*#', $url ) ) {
            $type = 'vimeo';
        }

        return $type;
    }

    public function get_youtube_id($url) {
        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
        return $match[1];
    }

    function get_the_video_markup($url = null) {
        if(!$url)
            return;

        $settings = '';
        $src = 'src';
        $type = $this->get_video_type($url);

        $output = '';

        $atts = '';

        if($type !== 'youtube' && $type !== 'vimeo'){

            $atts = 'controls';
            $output .= '<video class="video pfp-video" '.esc_attr($atts).' '.$src.'="'.esc_attr($url).'" type="video/'.esc_attr($type).'"></video>';

        }else {

            $id = $this->get_youtube_id($url);
            $poster = 'style="background: url(http://img.youtube.com/vi/'.$id.'/0.jpg) no-repeat cover;"';

            $settings = 'controls=1';
            $url = 'https://www.youtube.com/embed/'.$id.'?'.$settings;

            $output .= '<iframe class="video pfp-embed" '.$src.'="'.esc_attr($url).'" frameborder="0" height="100%" width="100%" allowfullscreen ></iframe>';

        }



        return $output;
    }



    private function youtubeEmbedLink($url)
    {
        $id = $this->get_youtube_id($url);
        return 'https://www.youtube.com/embed/'.$id;
    }



}

<?php
/**
 * WordPress runtime smoke tests for Video & Audio (disposable CI installation).
 *
 * @package WPEssential\Core
 */

use WPEssential\Core\Kit;

$wpe_check = static function ( $condition, $description ) {
	if ( ! $condition ) {
		throw new RuntimeException( 'FAIL: ' . $description );
	}
	echo 'PASS: ' . $description . "\n";
};

$wpe_block = static function ( array $attributes ) {
	return do_blocks( '<!-- wp:wpessential/video ' . wp_json_encode( (object) $attributes ) . ' /-->' );
};

$wpe_check( class_exists( Kit::class ), 'Kit loads' );
$wpe_check( isset( Kit::defs()['video'] ), 'Video definition loads' );
$wpe_check( WP_Block_Type_Registry::get_instance()->is_registered( 'wpessential/video' ), 'Gutenberg Video block registered' );
$wpe_check( wp_style_is( 'wpessential-kit-video', 'registered' ), 'Player CSS registered' );
$wpe_check( wp_script_is( 'wpessential-kit-video', 'registered' ), 'Click-to-load JS registered' );

$wpe_empty = $wpe_block( array() );
$wpe_check( false === strpos( $wpe_empty, '<figure' ), 'Empty URL does not render broken media' );

$wpe_self = $wpe_block( array(
	'source'           => 'file',
	'mediaType'        => 'video',
	'mediaUrl'         => 'https://cdn.example.org/clip.mp4',
	'title'            => 'Product film',
	'poster'           => array( 'url' => 'https://cdn.example.org/poster.jpg' ),
	'captionsUrl'      => 'https://cdn.example.org/captions.vtt',
	'captionsLanguage' => 'en-US',
	'captionsLabel'    => 'English subtitles',
	'preload'          => 'none',
	'loop'             => true,
	'muted'            => true,
	'description'      => 'Short product demo',
) );
$wpe_check( false !== strpos( $wpe_self, '<video ' ), 'HTML5 video player rendered' );
$wpe_check( false !== strpos( $wpe_self, 'controls playsinline' ), 'Native controls and playsinline enabled' );
$wpe_check( false !== strpos( $wpe_self, 'preload="none"' ), 'Self-hosted video respects preload setting' );
$wpe_check( false !== strpos( $wpe_self, 'src="https://cdn.example.org/clip.mp4"' ), 'Video source URL is safe and present' );
$wpe_check( false !== strpos( $wpe_self, 'poster="https://cdn.example.org/poster.jpg"' ), 'Poster URL rendered' );
$wpe_check( false !== strpos( $wpe_self, ' loop' ) && false !== strpos( $wpe_self, ' muted' ), 'Loop and mute options render' );
$wpe_check( false !== strpos( $wpe_self, '<track kind="captions"' ), 'WebVTT track supported' );
$wpe_check( false !== strpos( $wpe_self, 'srclang="en-US"' ), 'Caption language rendered' );
$wpe_check( false !== strpos( $wpe_self, 'Short product demo' ), 'Description renders' );

$wpe_audio = $wpe_block( array(
	'source' => 'file',
	'mediaType' => 'audio',
	'mediaUrl' => 'https://cdn.example.org/podcast.mp3',
	'captionsUrl' => 'https://cdn.example.org/subtitles.vtt',
) );
$wpe_check( false !== strpos( $wpe_audio, '<audio ' ), 'HTML5 audio player rendered' );
$wpe_check( false !== strpos( $wpe_audio, 'podcast.mp3' ), 'Audio source preserved' );
$wpe_check( false === strpos( $wpe_audio, '<track ' ), 'Video-only track omitted for audio' );
$wpe_check( false === strpos( $wpe_audio, '<video ' ), 'Audio source does not render video' );

$wpe_youtube = $wpe_block( array(
	'source' => 'youtube',
	'mediaUrl' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
	'title' => 'Launch video',
	'poster' => array( 'url' => 'https://cdn.example.org/cover.png' ),
) );
$wpe_check( false !== strpos( $wpe_youtube, 'youtube-nocookie.com/embed/dQw4w9WgXcQ' ), 'YouTube resolves to privacy-enhanced canonical embed URL' );
$wpe_check( false !== strpos( $wpe_youtube, 'data-wpe-embed=' ), 'Embed initializes only on click' );
$wpe_check( false === strpos( $wpe_youtube, '<iframe' ), 'YouTube iframe is NOT loaded in server HTML' );
$wpe_check( false !== strpos( $wpe_youtube, 'Play Launch video' ), 'Embed play button is accessible' );
$wpe_check( false !== strpos( $wpe_youtube, 'wpe-video__poster' ), 'Embed poster uses real safe image markup' );
$wpe_check( false !== strpos( $wpe_youtube, 'href="https://www.youtube.com/watch?v=dQw4w9WgXcQ"' ), 'Original video remains linked without JavaScript' );

$wpe_short = $wpe_block( array( 'source' => 'youtube', 'mediaUrl' => 'https://youtu.be/dQw4w9WgXcQ' ) );
$wpe_check( false !== strpos( $wpe_short, 'youtube-nocookie.com/embed/dQw4w9WgXcQ' ), 'Short YouTube links supported' );

$wpe_vimeo = $wpe_block( array( 'source' => 'vimeo', 'mediaUrl' => 'https://vimeo.com/12345678' ) );
$wpe_check( false !== strpos( $wpe_vimeo, 'player.vimeo.com/video/12345678' ), 'Vimeo URL canonicalized' );
$wpe_check( false !== strpos( $wpe_vimeo, 'dnt=1' ), 'Vimeo Do Not Track enabled' );
$wpe_check( false === strpos( $wpe_vimeo, '<iframe' ), 'Vimeo iframe not loaded on initial render' );

$wpe_invalid_hosts = $wpe_block( array( 'source' => 'youtube', 'mediaUrl' => 'https://www.youtube.com.evil.test/watch?v=dQw4w9WgXcQ' ) );
$wpe_check( false === strpos( $wpe_invalid_hosts, 'data-wpe-embed' ), 'Lookalike YouTube host rejected' );
$wpe_invalid_id = $wpe_block( array( 'source' => 'youtube', 'mediaUrl' => 'https://youtu.be/not-valid' ) );
$wpe_check( false === strpos( $wpe_invalid_id, 'data-wpe-embed' ), 'Invalid YouTube identifier rejected' );
$wpe_invalid_scheme = $wpe_block( array( 'source' => 'file', 'mediaUrl' => 'javascript:alert(1)' ) );
$wpe_check( false === strpos( $wpe_invalid_scheme, '<video ' ), 'Unsafe self-hosted media protocols rejected' );

$wpe_escaped = $wpe_block( array(
	'mediaUrl' => 'https://cdn.example.org/video.mp4',
	'description' => '<script>alert(1)</script><img src=x onerror=alert(2)>',
	'captionsUrl' => 'javascript:alert(3)',
	'captionsLanguage' => '" onload="alert(4)',
	'captionsLabel' => '" onerror="alert(5)',
) );
$wpe_check( false === strpos( $wpe_escaped, '<script>' ), 'Description HTML escaped' );
$wpe_check( false === strpos( $wpe_escaped, ' onerror="' ), 'Description attribute injection escaped' );
$wpe_check( false === strpos( $wpe_escaped, '<track ' ), 'Unsafe WebVTT URL rejected' );

$wpe_widget = class_exists( '\\Elementor\\Plugin' ) ? \Elementor\Plugin::$instance->widgets_manager->get_widget_types( 'wpe-video' ) : null;
$wpe_check( is_object( $wpe_widget ) && 'wpe-video' === $wpe_widget->get_name(), 'Elementor Video widget registered' );
$wpe_check( in_array( 'wpessential-kit-video', $wpe_widget->get_script_depends(), true ), 'Elementor declares click-to-load script dependency' );

$wpe_mapped = Kit::from_elementor( Kit::defs()['video'], array(
	'source' => 'file',
	'mediaType' => 'video',
	'mediaUrl' => 'https://cdn.example.org/elementor.mp4',
	'preload' => 'none',
	'muted' => 'yes',
	'maxWidth' => array( 'size' => 720, 'unit' => 'px' ),
) );
$wpe_check( true === $wpe_mapped['muted'], 'Elementor mute toggle maps to boolean' );
$wpe_check( 720 === $wpe_mapped['maxWidth'], 'Elementor player max width maps from slider' );
$wpe_elementor = Kit::render( 'video', $wpe_mapped );
$wpe_check( false !== strpos( $wpe_elementor, 'elementor.mp4' ), 'Elementor renders via shared Video template' );
$wpe_check( false !== strpos( $wpe_elementor, '--wpek-media-max:720px' ), 'Elementor sizing generates Kit CSS' );

echo "Video and Audio WordPress/Elementor smoke tests passed.\n";

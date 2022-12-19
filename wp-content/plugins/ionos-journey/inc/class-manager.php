<?php
namespace Ionos\Journey;

use Michelf\Markdown;

// Do not allow direct access!
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Manager class
 */
class Manager {

	public function __construct() {
        \add_action('admin_enqueue_scripts', array($this, 'enqueue_journey_resources'));

		if(isset($_GET['journey_persistance'])){
			$this->persist_progress($_GET['journey_persistance']);
			return;
		}

        if( isset($_GET['wp_tour']) ) {
            \add_action('admin_print_footer_scripts', array($this, 'get_json'), PHP_INT_MAX);
        } else {
            \add_action('admin_print_footer_scripts', array($this, 'add_start_button'), PHP_INT_MAX);
        }
	}

    public function get_json() {
	    if(\Ionos\Journey\Profile::isEnabled(\get_current_user_id(), 'journey_show')) {
		    global $current_screen;
		    global $_wp_admin_css_colors;

		    $main_js           = file_get_contents( \Ionos\Journey\Helper::get_js_path( 'player.js' ) );
		    $config_array      = Helper::get_configuration('journey');
		    $theme_array       = json_encode($_wp_admin_css_colors[\get_user_option('admin_color', \get_current_user_id())]);
		    $translation_array = Helper::get_configuration('interface');

		    if ( isset( $config_array[ $current_screen->id ] ) && ! empty( $config_array[ $current_screen->id ] ) ) {
			    $result_js = sprintf(
				    $main_js,
				    json_encode( $this->replace_markdown( $config_array[ $current_screen->id ] ), JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT ),
				    $theme_array,
				    json_encode($translation_array),
				    \get_user_meta(\get_current_user_id(), 'journey_progress', true),
				    \get_current_user_id()
			    );
			    echo '<script type="module"> ' . $result_js . '</script>';
		    }
	    }
    }

	/**
	 * Load internal resources
	 */
	public function enqueue_journey_resources() {
		\wp_enqueue_style( 
			'ionos-welcome',
			\Ionos\Journey\Helper::get_css_url( 'ionos-journey.css' ),
			array(),
			filemtime( \Ionos\Journey\Helper::get_css_path( 'ionos-journey.css' ) )
		);
	}

    public function add_start_button() {
	    if(\Ionos\Journey\Profile::isEnabled(\get_current_user_id(), 'journey_show')) {
			global $current_screen;

		    $language          = strtolower( explode( '_', \get_locale() )[0] );
			$config_array      = Helper::get_configuration('journey');
		    $translations      = Helper::get_configuration('interface');

			if(isset($config_array[$current_screen->id]) && !empty($config_array[$current_screen->id])) {
				$start_js = file_get_contents(\Ionos\Journey\Helper::get_js_path('parts/add_start.js'));
				echo '<script type="module"> ' . sprintf($start_js, $translations['name'])  . '</script>';
			}
		}
    }

	private function persist_progress($method = 'save'){
		if($method == 'save'){
			$postBody = file_get_contents("php://input");
			\update_user_meta(\get_current_user_id(), 'journey_progress', $postBody);
		}else if($method == 'clear'){
			\delete_user_meta(\get_current_user_id(), 'journey_progress');
		}
	}

    private function replace_markdown($json): array
    {
        $result = [];
        foreach ($json as $key => $value){
            if(is_array($value)){
                array_push($result, $this->replace_element($value));
            }else if($key == 'htmlContent'){
                array_push($result, str_replace("\n", "", Markdown::defaultTransform($value)));
            }else{
                array_push($result, $value);
            }
        }
        return $result;
    }

    private function replace_element($element){
        $result = $element;
        foreach ($element as $key => $value){
            if(is_array($value)){
                $result[$key] = $this->replace_element($value);
            }else if($key == 'htmlContent'){
                $result[$key] = str_replace("\n", "", Markdown::defaultTransform($value));
            }
        }
        return $result;
    }
}

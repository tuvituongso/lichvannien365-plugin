<?php
/*
Plugin Name: Lịch Vạn Niên 365 - Xem Ngày Tốt Xấu
Description: Hiển thị thông tin ngày tốt xấu từ lichvannien365.com. Có hỗ trợ shortcode, widget và tự động cập nhật từ GitHub.
Version: 1.0.0
Author: AI Assistant
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// Auto Update from GitHub
require plugin_dir_path(__FILE__) . 'plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\PluginUpdateCheckerFactory;

$updateChecker = PluginUpdateCheckerFactory::buildUpdateChecker(
    'https://github.com/yourusername/lichvannien365-plugin/',
    __FILE__,
    'lichvannien365'
);

// Main plugin logic
class LichVanNien365Plugin {
    private static $instance = null;

    public static function get_instance() {
        if ( self::$instance == null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_shortcode('lichvannien365', array($this, 'shortcode_display'));
        add_action('widgets_init', function() {
            register_widget('LichVanNien365_Widget');
        });
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }

    public function fetch_data($date) {
        $url = 'https://lichvannien365.com/ajax/load_ngay.php';
        $response = wp_remote_post($url, array(
            'body' => array('date' => $date),
            'headers' => array('Content-Type' => 'application/x-www-form-urlencoded')
        ));

        if ( is_wp_error( $response ) ) return 'Lỗi khi lấy dữ liệu.';
        return wp_remote_retrieve_body( $response );
    }

    public function shortcode_display($atts) {
        $atts = shortcode_atts(array('date' => date('d-m-Y')), $atts);
        return '<div class="lichvannien365-container">' . $this->fetch_data($atts['date']) . '</div>';
    }

    public function register_rest_routes() {
        register_rest_route('lichvannien365/v1', '/day/(?P<date>[^/]+)', array(
            'methods' => 'GET',
            'callback' => function($data) {
                return $this->fetch_data($data['date']);
            },
            'permission_callback' => '__return_true'
        ));
    }
}

class LichVanNien365_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct('lichvannien365_widget', 'Lịch Vạn Niên 365');
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
        echo do_shortcode('[lichvannien365]');
        echo $args['after_widget'];
    }
}

LichVanNien365Plugin::get_instance();
?>

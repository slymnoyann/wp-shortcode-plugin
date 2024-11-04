<?php
/*
Plugin Name: HTML Kısa Kod Dönüştürücü
Description: HTML kodlarını kısa kodlara dönüştürür ve yönetir
Version: 1.0
Author: Your Name
*/

if (!defined('ABSPATH')) exit;

class HTML_Shortcode_Converter {
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_shortcode('custom_html', array($this, 'render_shortcode'));
        add_action('wp_ajax_save_html_shortcode', array($this, 'save_html_shortcode'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'HTML Kısa Kod',
            'HTML Kısa Kod',
            'manage_options',
            'html-shortcode',
            array($this, 'admin_page'),
            'dashicons-html'
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        if ('toplevel_page_html-shortcode' !== $hook) {
            return;
        }
        
        wp_enqueue_style('html-shortcode-style', plugins_url('css/style.css', __FILE__));
        wp_enqueue_script('html-shortcode-script', plugins_url('js/script.js', __FILE__), array('jquery'), '1.0', true);
        wp_localize_script('html-shortcode-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>HTML Kısa Kod Dönüştürücü</h1>
            <div class="html-shortcode-container">
                <div class="form-group">
                    <label for="shortcode_name">Kısa Kod İsmi:</label>
                    <input type="text" id="shortcode_name" placeholder="Örnek: buton1">
                    <p class="description">Türkçe karakter kullanmayın (ç, ş, ı, ö, ü, ğ)</p>
                </div>
                <div class="form-group">
                    <label for="html_content">HTML Kodunuz:</label>
                    <textarea id="html_content" rows="10" placeholder="HTML kodunuzu buraya yapıştırın"></textarea>
                </div>
                <button id="save_shortcode" class="button button-primary">Kısa Kod Oluştur</button>
                
                <div id="shortcode_result" class="hidden">
                    <h3>Oluşturulan Kısa Kod:</h3>
                    <code id="shortcode_display"></code>
                    <button id="copy_shortcode" class="button">Kopyala</button>
                    <p class="description">Bu kısa kodu yazı veya sayfalarınıza ekleyebilirsiniz.</p>
                </div>

                <div class="shortcode-help">
                    <h3>Nasıl Kullanılır?</h3>
                    <ol>
                        <li>HTML kodunuzu yukarıdaki alana yapıştırın</li>
                        <li>Kısa kod için benzersiz bir isim belirleyin</li>
                        <li>Kısa Kod Oluştur butonuna tıklayın</li>
                        <li>Oluşturulan kısa kodu kopyalayın</li>
                        <li>Yazı veya sayfanıza yapıştırın</li>
                    </ol>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function save_html_shortcode() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Bu işlem için yetkiniz bulunmuyor.');
        }
        
        $name = sanitize_text_field($_POST['name']);
        $html = wp_kses_post($_POST['html']);
        
        if (empty($name) || empty($html)) {
            wp_send_json_error('Lütfen tüm alanları doldurun.');
        }
        
        $saved_codes = get_option('html_shortcode_data', array());
        $saved_codes[$name] = $html;
        
        update_option('html_shortcode_data', $saved_codes);
        
        wp_send_json_success(array(
            'message' => 'Kısa kod başarıyla oluşturuldu!',
            'shortcode' => '[custom_html name="' . $name . '"]'
        ));
    }
    
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'name' => '',
        ), $atts);
        
        $saved_codes = get_option('html_shortcode_data', array());
        
        if (empty($atts['name']) || !isset($saved_codes[$atts['name']])) {
            return '';
        }
        
        return $saved_codes[$atts['name']];
    }
}

HTML_Shortcode_Converter::get_instance();
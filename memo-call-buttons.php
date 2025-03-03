<?php
/**
 * Plugin Name: Hamdy Call Buttons
 * Description: Add a call button on the mobile phone, and set custom numbers for articles and pages.
 * Version: 1.0
 * Author: Hamdy Abu Khadra
 * Author URI: https://www.facebook.com/HamdyKhadra
 * Text Domain: hamdy-call-buttons
 */

function hamdy_call_buttons_load_textdomain() {
    load_plugin_textdomain('hamdy-call-buttons', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'hamdy_call_buttons_load_textdomain');


define('HAMDY_CALL_BUTTON_VERSION', '1.0');

function add_custom_call_meta(){
    $post_types = array ( 'post', 'page' );
    add_meta_box(
    "ms-meta-phone", // $id
    __('رقم الهاتف', 'hamdy-call-buttons'),
    "ms_phone_meta_box_markup", // $callback
    $post_types, // post type
    "side", // $context
    "high" // $priority
    );
}
add_action("add_meta_boxes", "add_custom_call_meta");

function ms_phone_meta_box_markup($post){
    wp_nonce_field( '_ms_meta_phone_nonce', 'ms_meta_phone_nonce' );
    ?>
    <p>
    <label for="ms-meta-phone"><?php _e('رقم الهاتف:', 'hamdy-call-buttons'); ?></label>
        <input name="ms-meta-phone" type="text" value="<?= get_post_meta($post->ID, "ms-meta-phone", true); ?>">
    </p>
    <?php
}

function save_ms_phone_meta_box( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! isset( $_POST['ms_meta_phone_nonce'] ) || ! wp_verify_nonce( $_POST['ms_meta_phone_nonce'], '_ms_meta_phone_nonce' ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    if ( isset( $_POST['ms-meta-phone'] ) )
        update_post_meta( $post_id, 'ms-meta-phone', esc_attr( $_POST['ms-meta-phone'] ) );
    else
        update_post_meta( $post_id, 'ms-meta-phone', null );
}
add_action("save_post", "save_ms_phone_meta_box");


class MS_Custom_Call_Button {
    private $plugin_path;
    private $plugin_url;
    private $ms_options;
    
    public function __construct() {

         // تحميل ملفات الترجمة للإضافة
        add_action('plugins_loaded', [$this, 'load_textdomain']);

        // grab the options, use for entire object
        $this->ms_options = $this->ms_options();
        // admin
        add_action( 'admin_menu',[ $this, 'ms_add_menu' ] );

        // create needed initialization
        add_action('admin_init', [  $this, 'ms_register_options_settings'] );

        // add custom call button
        add_action('wp_footer', [ $this, 'ms_add_buttons'], 10);



       
         $button_color = esc_attr($this->ms_options['button_color']);

        // grab the options, use for entire object
        $this->ms_options = $this->ms_options();

        add_action('wp_head', [  $this, 'ms_call_button'], 100);

    }


    public function load_textdomain() {
        load_plugin_textdomain('hamdy-call-buttons', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function ms_add_menu(){

        add_options_page(__('إعدادات زر الإتصال', 'hamdy-call-buttons'), __('حمدي زر الإتصال', 'hamdy-call-buttons'), 'publish_posts', 'ms_custom_call_button', [$this, 'ms_options_page']);
    }

    public function ms_call_button() {
        $button_position = $this->ms_options['button_position']; 
        $button_color = esc_attr($this->ms_options['button_color']);
    
        if ($button_color == "#ffffff" || $button_color == "#fff") {
            $button_color = "#25D366"; 
        }
    
        $position_class = ($button_position == 'left') ? 'left' : 'right';
    
        // Determine location based on settings
        $position_class = '';
        if ($button_position == 'left') {
            $position_class = 'left';
        } else {
            $position_class = 'right';
        }

    
        echo '<style>


       .ms-call-txt, .ms-whats-txt {
            background: ' . $button_color . ' !important;
            color: #fff !important; 
        }
        .ms-call-icon svg {
            fill: ' . $button_color . ' !important;
        }
        .ms-call-button.active .ms-call-txt,
        .ms-whats-button.active .ms-whats-txt,
        .ms-call-button:hover .ms-call-txt,
        .ms-whats-button:hover .ms-whats-txt {
            box-shadow: 0 8px 16px 0 rgba(0, 0, 0, .2), 0 6px 20px 0 rgba(0, 0, 0, .19);
        }
            .txt-center{
                text-align: center;
            }
            .txt-center ul li{
                list-style: none;
            }
            .clear:after,
            .clear:before {
                content: " ";
                display: table;
                table-layout: fixed;
            }
            .clear:after {
                clear: both;
            }
            .ms-whats-button,
            .ms-call-button {
                display: flex;
                position: fixed;
                bottom: 0.3em;
                outline: 0;
                cursor: pointer;
                z-index: 9999;
            }
            .ms-call-button{
                bottom: 4.2em;
            }
            .ms-call-icon{
                display: inline-block;
                position: relative;
                width: 45px;
                height: 44px;
                text-align: center;
                border-radius: 50%;
                background-color: white;
                -webkit-box-shadow: 1px 1px 6px 0px rgba(68, 68, 68, 0.705);
                -moz-box-shadow: 1px 1px 6px 0px rgba(68, 68, 68, 0.705);
                box-shadow: 1px 1px 6px 0px rgba(68, 68, 68, 0.705);
            }
            .ms-whats-txt,
            .ms-call-txt{
                padding: 0 8px 0 20px;
                font-size: 15px;
                font-weight: 600;
                display: inline-block;
                background: #00e676;
                color: #fff;
                margin-left: -15px;
                line-height: 28px;
                border-radius: 10px;
                height: 31px;
                margin-top: 6px;
            }
            .ms-whats-txt{
                padding: 0 15px 0 25px;
            }
            .ms-call-txt{
                background: linear-gradient(to top, #d83f91, #d0409b, #c743a5, #bb47af, #ae4bb8);
            }
            .ms-call-button.active .ms-call-txt,
            .ms-whats-button.active .ms-whats-txt,
            .ms-call-button:hover .ms-call-txt,
            .ms-whats-button:hover .ms-whats-txt{
                -webkit-box-shadow: 0 8px 16px 0 rgba(0, 0, 0, .2), 0 6px 20px 0 rgba(0, 0, 0, .19);
                box-shadow: 0 8px 16px 0 rgba(0, 0, 0, .2), 0 6px 20px 0 rgba(0, 0, 0, .19);
            }
    
            .ms-call-button.left, .ms-whats-button.left {
                left: 6px;
            }
    
            .ms-call-button.right, .ms-whats-button.right {
                right: 6px;
            }
    
            /* Customization of the site based on settings*/
            .ms-call-button.' . $position_class . ', .ms-whats-button.' . $position_class . ' {
                position: fixed;
            }
        </style>';

    }
    function ms_options_page(){

        if ( !(isset($_GET['page']) && $_GET['page'] == 'ms_custom_call_button' )) 
            return;
        

if (isset($_GET['color_reset']) && $_GET['color_reset'] == "1") {
    echo '<div class="updated notice is-dismissible"><p>' . __('تمت إعادة اللون الافتراضي بنجاح!', 'hamdy-call-buttons') . '</p></div>';

}
?>
    
        <div class='wrap'>
        <h1><?php _e('إعدادات زر الاتصال', 'hamdy-call-buttons'); ?></h1>
         <form method="post" action="options.php" class="form-table">
            <?php
            wp_nonce_field('ms_options');
            settings_fields('ms_custom_options-group');
            ?>
            <table border=0 cellpadding=2 cellspacing="2">
                <tr>
                <th><?php _e('حالة الزر', 'hamdy-call-buttons'); ?></th>
                    <td class="activated">
                    <input name="ms_options[active]" type="checkbox" value="1" <?php checked('1', $this->ms_options['active']); ?> />
                    <label for="activated"><?php echo ($this->ms_options['active'] > 0) ? __('مفعل', 'hamdy-call-buttons') : __('تفعيل', 'hamdy-call-buttons'); ?></label>
                </tr> 
            </table>

            <table border=0 cellpadding=2 cellspacing="2">
            <tr>
            <th><?php _e('الرقم الأساسي', 'hamdy-call-buttons'); ?></th>

                <td>
                <input name="ms_options[phone_number]" type="text" value="<?php echo esc_attr($this->ms_options['phone_number']); ?>" />                </td>
            </tr>

            <tr>
            <th><?php _e('مكان الأزرار', 'hamdy-call-buttons'); ?></th>

    <td>
        <select name="ms_options[button_position]">
        <option value="right" <?php selected('right', $this->ms_options['button_position']); ?>><?php _e('يمين', 'hamdy-call-buttons'); ?></option>
        <option value="left" <?php selected('left', $this->ms_options['button_position']); ?>><?php _e('يسار', 'hamdy-call-buttons'); ?></option>
        </select>
    </td>
</tr>

<tr>
<th><?php _e('نص الزر', 'hamdy-call-buttons'); ?></th>

    <td>
    <input name="ms_options[button_text]" type="text" value='<?= esc_attr($this->ms_options['button_text']); ?>' placeholder="<?php _e('إتصل الآن', 'hamdy-call-buttons'); ?>" />
    </td>
</tr>

<tr>
<th><?php _e('لون الزر', 'hamdy-call-buttons'); ?></th>
    <td>
        <input name="ms_options[button_color]" type="color" value='<?= esc_attr($this->ms_options['button_color']); ?>' />
        <button type="submit" name="reset_color" value="1" class="button-secondary"><?php _e('إرجاع اللون الافتراضي', 'hamdy-call-buttons'); ?></button>
        </td>
</tr>



            </table>            

            <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('حفظ الإعدادات', 'hamdy-call-buttons'); ?>" />
            </p>

            </form>  
             <hr>
             <div class="donate" style="text-align: center;">
                <strong><a href="https://www.facebook.com/fawaz4o4dev"><?php _e('برمجة بواسطة حمدي أبو خضرة', 'hamdy-call-buttons'); ?></a></strong>
                <p><?php _e('الإصدار', 'hamdy-call-buttons'); ?> <?php echo HAMDY_CALL_BUTTON_VERSION; ?></p>
            </div>
                      
        </div>
        
        <?php

    }

    function ms_register_options_settings() { 
        register_setting('ms_custom_options-group', 'ms_options');

    // Check if the user pressed the "return default color" button
    if (isset($_POST['reset_color']) && $_POST['reset_color'] == "1") {
        $default_options = get_option('ms_options', []);
        $default_options['button_color'] = '#d83f91'; 
        update_option('ms_options', $default_options);

        // Redirect the page to prevent the request from being resubmitted upon refresh
        wp_redirect(admin_url('options-general.php?page=ms_custom_call_button&color_reset=1'));
        exit;
    }
        
    
        // Ensure that the color is preserved
        if (!get_option('ms_options')['button_color']) {
            update_option('ms_options', array_merge(get_option('ms_options', []), ['button_color' => '#d83f91']));
        }
    }
    


    function ms_options() { 
        $defaults = array(
            'active' => '0',
            'phone_number'  => '',
           'button_text' => __('إتصل الآن', 'hamdy-call-buttons'), // Default text with translation support
            'button_color' => '#d83f91', // Default color of the button
        );
    
        // Fetch stored settings
        $ms_options = get_option('ms_options', []);     
        if (is_array($ms_options)) {
            foreach ($defaults as $k => $v) {
                if (!isset($ms_options[$k]) || empty($ms_options[$k])) {
                    $ms_options[$k] = $v;
                }
            }
        } else {
            $ms_options = $defaults;
        }


  if (!isset($ms_options['button_color']) || empty($ms_options['button_color'])) {
        $ms_options['button_color'] = '#d83f91'; 
    }

    return $ms_options;
}
    
    function get_meta_phone(){
        global $wp_query;
        
        $post_id = $wp_query->get_queried_object_id();

        $me_phone_meta =  get_post_meta($post_id, "ms-meta-phone", true);

        if ( !empty($me_phone_meta) )  {

             return $this->ms_sanitize_phone($me_phone_meta);

        }else{
            
            return $this->ms_sanitize_phone($this->ms_options['phone_number']);
        
        }

    }
    function ms_sanitize_phone($number) {

        return filter_var($number, FILTER_SANITIZE_NUMBER_INT);
    }


    function ms_mandatory_have_info() {
        return (($this->ms_options['active'] > 0 ) ) ? true : false;
    }

    function ms_add_buttons() {
        if(is_feed()){
            return;
        }
    
        $return = "\n\n";
    
        if ($this->ms_mandatory_have_info()) {
            $position = ($this->ms_options['button_position'] === 'left') ? 'left' : 'right';
            $button_color = esc_attr($this->ms_options['button_color']);
    
            // Make sure the chosen color is not white, otherwise a default color will be set
            if ($button_color == "#ffffff" || $button_color == "#fff") {
                $button_color = "#25D366"; 
            }
    
            $return .= '<div class="clear">
                <a href="tel:'.$this->get_meta_phone().'" class="ms-call-button ' . $position . '">
                    <span class="ms-call-txt">' . esc_html($this->ms_options['button_text']) . '</span>
                    <span class="ms-call-icon" aria-hidden="true">
                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="28" height="45" viewBox="0 0 512 512" style="fill: ' . $button_color . ';">
                            <path d="M352 320c-32 32-32 64-64 64s-64-32-96-64-64-64-64-96 32-32 64-64-64-128-96-128-96 96-96 96c0 64 65.75 193.75 128 256s192 128 256 128c0 0 96-64 96-96s-96-128-128-96z"></path>
                        </svg>
                    </span>
                </a>
                <a href="https://api.whatsapp.com/send?phone='.$this->get_meta_phone().'" class="ms-whats-button ' . $position . '" target="_blank" rel="noopener noreferrer">
                    <span class="ms-whats-txt">' . esc_html($this->ms_options['button_text']) . '</span>
                    <span class="ms-call-icon" aria-hidden="true">
                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="45" height="45" viewBox="0 0 24 24" style="fill: ' . $button_color . ';">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"></path>
                        </svg>
                    </span>
                </a>
            </div>';
        }
    
        echo $return;
    }
    
    

  
}

new MS_Custom_Call_Button();
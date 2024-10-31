<?php

/*

*Plugin Name: ProductViewer
*Description: Product 3D Viewer, based on three.js. 
*Version: 1.0.0
*Author: Ofek Nakar
*Author URI: https://www.loudguys.co
*Plugin URI:/ProductViewer
*Text Domain: ProductViewer
* License: GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html



*/

if( !defined('ABSPATH')) exit;



class productViewer{


 


    function __construct(){
      

        add_action('admin_init',array($this,'adminAssets'));
        add_action('wp_body_open',array($this,'frontAssets'));
        add_action( 'woocommerce_product_quick_edit_start', array($this,'product_option_group_quick') );

        add_action( 'woocommerce_before_add_to_cart_form',array($this,'singleProductViewerHtml'));
        add_action( 'woocommerce_product_options_general_product_data', array($this,'product_option_group') );
        add_action( 'woocommerce_process_product_meta', array($this,'product_option_group_save_fields'), 10, 2 );
        add_action( 'woocommerce_product_quick_edit_save', array($this,'product_option_group_save_fields_quick'));

        add_action('admin_menu', array( $this, 'adminMenuInit'));
        

        // replace image thumbnail
        add_filter( 'woocommerce_single_product_image_thumbnail_html', array($this,'remove_single_product_image'), 10, 2 );


        // 1. Show custom input field above Add to Cart
 
      add_action( 'woocommerce_before_add_to_cart_button', array($this,'viewer_product_add_on'), 9 );
       // -----------------------------------------
       // 2. Throw error if custom input field empty
 
      add_filter( 'woocommerce_add_to_cart_validation', array($this,'viewer_product_add_on_validation'), 10, 3 );
       // -----------------------------------------
       // 3. Save custom input field value into cart item data
 
      add_filter( 'woocommerce_add_cart_item_data', array($this,'viewer_product_add_on_cart_item_data'), 10, 2 );
       // -----------------------------------------
       // 4. Display custom input field value @ Cart
 
      add_filter( 'woocommerce_get_item_data', array($this,'viewer_product_add_on_display_cart'), 10, 2 );
 
       // -----------------------------------------
       // 5. Save custom input field value into order item meta
 
      add_action( 'woocommerce_add_order_item_meta', array($this,'viewer_product_add_on_order_item_meta'), 10, 2 );
       // -----------------------------------------
       // 6. Display custom input field value into order table
 
      add_filter( 'woocommerce_order_item_product', array($this,'viewer_product_add_on_display_order'), 10, 2 );
       // -----------------------------------------
       // 7. Display custom input field value into order emails
 
      add_filter( 'woocommerce_email_order_meta_fields', array($this,'viewer_product_add_on_display_emails') );
 
    }
 
    function adminAssets(){
        wp_enqueue_script('threeScript',plugin_dir_url(__FILE__). 'three/three.js',array());
        wp_enqueue_script('controlsScript',plugin_dir_url(__FILE__). 'three/controls.js',array());
        wp_enqueue_script('zilbScript',plugin_dir_url(__FILE__). 'three/zilb.js',array());
        wp_enqueue_script('glbScript',plugin_dir_url(__FILE__). 'three/glb.js',array());
        wp_enqueue_script('fbxScript',plugin_dir_url(__FILE__). 'three/fbx.js',array());
        wp_enqueue_script('viewerAdminScript',plugin_dir_url(__FILE__). 'js/viewerAdmin.js',array());
        wp_enqueue_style('adminStyles',plugin_dir_url(__FILE__). 'css/adminStyles.css', array(), '1.0.0', 'all' );

        add_settings_section( 'product_viewer_section',null , null, 'product-viewer-page' );


        // add_settings_field('pv_woo_themecolor', null,array($this,'staticAdminHTML'),'product-viewer-page', 'product_viewer_section');
        // register_setting('productviewerwooplugin', 'pv_woo_themecolor', array('sanitize_callback' => 'sanitize_hex_color', 'default' =>'#ffffff') ) ;
    }


    function staticAdminHTML()
    {
        ?>
    <input  type="color" id="pv_woo_themecolor" name="pv_woo_themecolor" style="display:none;" value="<?php echo esc_attr(get_option('pv_woo_themecolor')); ?>" >
    
    
    
        <?php
    
    }
    function adminMenuInit(){
        add_menu_page('ProductViewer Settings','ProductViewer', 'manage_options' ,'product-viewer-page',array($this,'adminHTML'),plugin_dir_url(__FILE__) . 'menuIcon.svg',100);

    }
    function adminHTML(){
        ?>
             
           <div style="position:relative;display:block;font-size:4em;font-weight:700;">Product Viewer <img style="position:relative;top:45px;"width="150" height="150" src="<?php echo plugin_dir_url(__FILE__). 'icon.png' ?>"></div>
            

           <p class="desc_note">
            Product Viewer is an easy way to add 3d model as your product,
            your customers can customize your 3d product with colors,
            you can select from 14 colors right now.
          </p>
          <p class="desc_note">If you like this plugin consider supporting <a href="https://maple-wp.com">MapleWP</a> </p>
           <p class="desc">
            Product Viewer HOW TO INSTALL<br>
            1. activate Product Viewer<br>
            2. edit you product<br>
            3. select colors and model<br>
            4. show your 3d product to the world
           </p>
           <div class="tutorial_vid"></div>
              
           <a href="https://maple-wp.com"class="maple_logo"></a>


        <?php

    }

    function frontAssets(){
        wp_enqueue_style('frontStyles',plugin_dir_url(__FILE__). 'css/frontStyles.css', array(), '1.0.0', 'all' );
        wp_enqueue_script('threeScript',plugin_dir_url(__FILE__). 'three/three.js',array());
        wp_enqueue_script('controlsScript',plugin_dir_url(__FILE__). 'three/controls.js',array());
        wp_enqueue_script('zilbScript',plugin_dir_url(__FILE__). 'three/zilb.js',array());
        wp_enqueue_script('glbScript',plugin_dir_url(__FILE__). 'three/glb.js',array());
        wp_enqueue_script('fbxScript',plugin_dir_url(__FILE__). 'three/fbx.js',array());
        wp_enqueue_script('viewerFrontScript',plugin_dir_url(__FILE__). 'js/viewerFront.js',array());

    }

    function singleProductViewerHtml(){
        global $product;
    $id = $product->get_id();
    $product_attr = get_post_meta( $id, 'model_url');
    $display= get_post_meta( $id, 'display_model',true);
    $display_inst= get_post_meta( $id, 'display_model_instead',true);

    if($display == 'yes' ){

        if($display_inst !== 'yes'){
          ?>
         <div class="model_viewer_product_button">Preview 3D</div>
         <div id="model_viewer_product_container" data-display="<?php echo esc_attr($display); ?>" data-model="<?php echo esc_url($product_attr[0]) ?>" >
       <canvas class="viewer_canvas" width="500" height="400"></canvas>
         <a href="https://maple-wp.com" class="credit_viewer_front">Powerd by MapleWP</a>
      </div>
          <?php
        }
        else{
            ?>
         <div id="model_viewer_product_container"data-inst="<?php echo esc_attr($display_inst); ?>"  data-display="<?php echo esc_attr($display); ?>" data-model="<?php echo esc_url($product_attr[0]) ?>" >
        </div>
            <?php
        }
        
        ?>

           
   
   

     <?php
    }
      
    }


    function product_option_group(){
       ?>
     <div class="model_product_viewer">
     <h1>Product Viewer</h1>


     <div class="options_group">
     <?php
     
        woocommerce_wp_text_input( array(
            'id'      => 'model_url',
            'value'   => get_post_meta( get_the_ID(), 'model_url', true ),
            'label'   => '3D Product Url',
            'desc_tip' => true,
            'description' => 'Enter you product 3D Model, insure your using model with high quality.',
        ) );
        ?>
        <canvas class="canvas_admin_viewer" ></canvas>
         <p>Select colors </p>
        <div class="color_product_viewer_box">
        <div class="color_product_viewer_box_single color_product_viewer_box_single0"><div class="checks"></div></div>
        <div class="color_product_viewer_box_single color_product_viewer_box_single1"><div class="checks"></div></div>
        <div class="color_product_viewer_box_single color_product_viewer_box_single2"><div class="checks"></div></div>
        <div class="color_product_viewer_box_single color_product_viewer_box_single3"><div class="checks"></div></div>
        <div class="color_product_viewer_box_single color_product_viewer_box_single4"><div class="checks"></div></div>
        <div class="color_product_viewer_box_single color_product_viewer_box_single5"><div class="checks"></div></div>
        <div class="color_product_viewer_box_single color_product_viewer_box_single6"><div class="checks"></div></div>
        <div class="color_product_viewer_box_single color_product_viewer_box_single7"><div class="checks"></div></div>
        <div class="color_product_viewer_box_single color_product_viewer_box_single8"><div class="checks"></div></div>
        <div class="color_product_viewer_box_single color_product_viewer_box_single9"><div class="checks"></div></div>
        <div class="color_product_viewer_box_single color_product_viewer_box_single10"><div class="checks"></div></div>
        <div class="color_product_viewer_box_single color_product_viewer_box_single12"><div class="checks"></div></div>
        <div class="color_product_viewer_box_single color_product_viewer_box_single11"><div class="checks"></div></div>
        <div class="color_product_viewer_box_single color_product_viewer_box_single13"><div class="checks"></div></div>
        </div>

        <p>Select part of the model you want to manipulate</p>
        <ul class="viewer_model_childs"></ul>
        <?php
          woocommerce_wp_text_input( array(
            'id'      => 'model_colors',
            'value'   => get_post_meta( get_the_ID(), 'model_colors', true ),
            'label'   => 'Colors Product',
            'desc_tip' => true,
            'description' => 'Enter you product 3D Model, insure your using model with high quality.',
        ) );
        woocommerce_wp_text_input( array(
            'id'      => 'model_part_color',
            'value'   => get_post_meta( get_the_ID(), 'model_part_color', true ),
            'label'   => 'Colors Product',
            'desc_tip' => true,
            'description' => 'Enter you product 3D Model, insure your using model with high quality.',
        ) );
     
        woocommerce_wp_checkbox( array(
            'id'      => 'display_model',
            'value'   => get_post_meta( get_the_ID(), 'display_model', true ),
            'label'   => 'Display Model Preview',
            'desc_tip' => true,
            'description' => 'uncheck if you want to disable model on single prodcut page.',
        ) );
        woocommerce_wp_checkbox( array(
            'id'      => 'display_model_instead',
            'value'   => get_post_meta( get_the_ID(), 'display_model_instead', true ),
            'label'   => 'Model Thumbnail',
            'desc_tip' => true,
            'description' => 'uncheck if you want to disable model on thumbnail image.',
        ) );
        ?>
        </div>


        <a class="credit_link_viewer" href="https://www.maple-wp.com">Made with ♥ by <span class="maple_wp_c">MapleWP</span></a>

        </div>
        <?php
    }



    function product_option_group_quick(){
        ?>
      <div class="model_product_viewer">
      <h1>Product Viewer</h1>
 
 
      <div class="options_group">
      <?php
      
         woocommerce_wp_text_input( array(
             'id'      => 'model_url',
             'value'   => get_post_meta( get_the_ID(), 'model_url', true ),
             'label'   => '3D Product Url',
             'desc_tip' => true,
             'description' => 'Enter you product 3D Model, insure your using model with high quality.',
         ) );
         ?>
         <?php
         woocommerce_wp_checkbox( array(
             'id'      => 'display_model',
             'value'   => get_post_meta( get_the_ID(), 'display_model', true ),
             'label'   => 'Model Display',
             'desc_tip' => true,
             'description' => 'uncheck if you want to disable model on single prodcut page.',
         ) );
      
         ?>
         </div>
 
 
         <a class="credit_link_viewer" href="https://www.maple-wp.com">Made with ♥ by <span class="maple_wp_c">MapleWP</span></a>
 
         </div>
         <?php
     }









    function product_option_group_save_fields($id,$post){
		update_post_meta( $id, 'model_url', sanitize_text_field($_POST['model_url']) );
		update_post_meta( $id, 'model_colors', sanitize_text_field($_POST['model_colors'] ));
		update_post_meta( $id, 'display_model', sanitize_text_field($_POST['display_model'] ));
		update_post_meta( $id, 'model_part_color', sanitize_text_field($_POST['model_part_color']) );
        update_post_meta( $id, 'display_model_instead', sanitize_text_field($_POST['display_model_instead'] ));


    }
    function product_option_group_save_fields_quick($product){
        $post_id = $product->get_id();
        if ( isset( $_REQUEST['model_url'] ) ) {
            $custom_field = sanitize_text_field($_REQUEST['model_url']);
            $custom_field1 = sanitize_text_field($_REQUEST['display_model']);
            update_post_meta( $post_id, 'model_url', wc_clean( $custom_field ) );
            update_post_meta( $post_id, 'display_model', wc_clean( $custom_field1 ) );

        }

    }


    function viewer_product_add_on() {

        global $product;
        $id = $product->get_id();
        $product_attr = get_post_meta( $id, 'model_url');
        $display= get_post_meta( $id, 'display_model',true);
        $value = isset($_POST['custom_text_add_on'] ) ? sanitize_hex_color( $_POST['custom_text_add_on'] ) : '';
        $colors= get_post_meta( $id, 'model_colors',true);
        $part= get_post_meta( $id, 'model_part_color',true);
    
          if($display == 'yes'){
            ?>
            <div><label>Product Color<abbr class="required" title="required">*</abbr></label>
                
            <div class="color_product_viewer_box" data-colors="<?php echo esc_attr($colors) ?>" data-part="<?php echo esc_attr($part) ?>">
         
            </div>
            <input style="top:-11px;"  type="color" class="color_product_viewer_box_singled" id="product_viewer_color_select" name="custom_product_color" value="<?php esc_attr($value) ?>">
        
            <p></p></div>
            <?php
          }
      
    }

    function remove_single_product_image( $html, $thumbnail_id ) {
        global $product;
        $id = $product->get_id();
        $display= get_post_meta( $id, 'display_model_instead',true);
    
        if($display == 'yes'){
            return '
            <canvas class="viewer_canvas" width="650" height="500" tabindex="0" style="width: 650px; height: 500px;"></canvas>
            <p class="credit_viewer_front"></p>
            ';
        }
        return $html;
     
    }
    
    function viewer_product_add_on_validation( $passed, $product_id, $qty ){
        if( isset( $_POST['custom_product_color'] ) && sanitize_hex_color( $_POST['custom_product_color'] ) == '' ) {
           wc_add_notice( 'Custom Text Add-On is a required field', 'error' );
           $passed = false;
        }
        return $passed;
     }


     function viewer_product_add_on_cart_item_data( $cart_item, $product_id ){
        if( isset( $_POST['custom_product_color'] ) ) {
            $cart_item['custom_product_color'] = sanitize_hex_color( $_POST['custom_product_color'] );
        }
        return $cart_item;
    }
     
    function viewer_product_add_on_display_emails( $fields ) { 
        $fields['custom_text_add_on'] = 'Custom Text Add-On';
        return $fields; 
    }
    function viewer_product_add_on_display_order( $cart_item, $order_item ){
        if( isset( $order_item['custom_product_color'] ) ){
            $cart_item['custom_product_color'] = $order_item['custom_product_color'];
        }
        return $cart_item;
    }
    function viewer_product_add_on_order_item_meta( $item_id, $values ) {
        if ( ! empty( $values['custom_product_color'] ) ) {
            wc_add_order_item_meta( $item_id, 'Custom product color', $values['custom_product_color'], true );
        }
    }
    function viewer_product_add_on_display_cart( $data, $cart_item ) {
        if ( isset( $cart_item['custom_product_color'] ) ){
            $data[] = array(
                'name' => 'Product color',
                'value' => sanitize_hex_color( $cart_item['custom_product_color'] )
            );
        }
        return $data;
    }

}



if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

     // check if woocommerce activated and init product viewer class
    $productViewer = new productViewer();


 
  }


 





 

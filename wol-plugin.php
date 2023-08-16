<?php

/**
 * 
 * Plugin Name: Wol Plugin
 * Description: 
 * Version: 1.0.0
 * Text Domain: options-plugin
 * 
 * 
 */


$current_user_id = -1;
function current_user_id(){
    global $current_user_id;
    $current_user_id = wp_get_current_user()->ID;
}


function get_dirs(){
    $dirs = plugin_dir_url(__FILE__). "|". get_rest_url();
    echo $dirs;
}


$desktops_json = file_get_contents(plugin_dir_path( __FILE__ )."desktops.json");
$desktops = json_decode($desktops_json, true);

add_action("plugins_loaded", "create_wol_settings");

function create_wol_settings(){
    current_user_id();
    add_action("rest_api_init", "create_settings_endpoints");
    add_action('admin_menu', 'register_wol_settings_page');
    
}

function create_settings_endpoints(){
    register_rest_route("v1/wol", "adddesktop", array(
        "methods" => "POST",
        "callback" => "add_desktop"
    ));
    register_rest_route("v1/wol", "deldesktop", array(
        "methods" => "POST",
        "callback" => "del_desktop"
    ));
    register_rest_route("v1/wol", "createadminnonce", array(
        "methods" => "POST",
        "callback" => "create_admin_nonce"
    ));
}



function register_wol_settings_page() {
    add_options_page(
        'Wol settings',         
        'Wol settings',         
        'manage_options',          
        'wol_settings',      
        'wol_settings_page'  
    );
}

function wol_settings_page(){
    include plugin_dir_path( __FILE__ ) . "static/settings.php";
}



function add_desktop($data){
    global $desktops;
    global $current_user_id;
    $params = $data -> get_params();
    wp_set_current_user($current_user_id);
    $nonce = $params["admin_nonce"];
    $action = $params["action"];
    if (wp_verify_nonce($nonce, "admin_nonce")){
        if ($action == "add"){
            if (!isset($desktops[$params["name"]])){
                $desktops[$params["name"]] = array(
                    "ip" => $params["ip"],
                    "mac" => $params["mac"]
                );
                echo "success_add";
            }
            else {
                echo "error_already_exists";
            }
        }
        elseif ($action == "update"){   
            if (isset($desktops[$params["name"]])){
                $desktops[$params["name"]] = array(
                    "ip" => $params["ip"],
                    "mac" => $params["mac"]
                );
                echo "success_update";
            }
            else {
                echo "error_non_existent";
            }
        }
        else {
            echo "action_error";
        }
        $desktops_jsonstr = json_encode($desktops);
        file_put_contents(plugin_dir_path( __FILE__ )."desktops.json", $desktops_jsonstr);
    }
    else {
        echo "nonce_error";
    }
}


function del_desktop($data){
    global $desktops;
    global $current_user_id;
    $params = $data -> get_params();
    $nonce = $params["admin_nonce"];
    wp_set_current_user($current_user_id);
    if (wp_verify_nonce($nonce, "admin_nonce")){
        if (isset($desktops[$params["name"]])){
            unset($desktops[$params["name"]]);
            $desktops_jsonstr = json_encode($desktops);
            file_put_contents(plugin_dir_path( __FILE__ )."desktops.json", $desktops_jsonstr);
            echo "success_del";
        }
        else {
            echo "error_non_existent";
        }
    }
    else {
        echo "nonce_error";
    }
}


if (!defined("ABSPATH")){
    die("404 Not Found");
}



class DesktopStatuses {
    public function __construct(){
        add_action("plugins_loaded", "create_statuses");
    }
}
new DesktopStatuses;



function create_statuses(){
    add_shortcode("statuses", "show_desktop_statuses");
    add_action("rest_api_init", "create_rest_endpoints");
    add_action("wp_enqueue_scripts", "enqueue_statuses_css");
    add_action("wp_enqueue_scripts", "enqueue_statuses_js");
    current_user_id();
}


function enqueue_statuses_js(){
    wp_enqueue_script("statuses_script", plugin_dir_url(__FILE__) . "/static/statuses.js", array(), time(), "true");
}

function enqueue_statuses_css(){
    wp_enqueue_style("statuses_display", plugin_dir_url(__FILE__) . "/static/style.css", array(), time());
}


function show_desktop_statuses(){
    ob_start();
    include plugin_dir_path( __FILE__ ) . "static/statuses.php";
    return ob_get_clean();
}



function create_rest_endpoints(){
    register_rest_route("v1/wol", "getloginstatus", array(
        "methods" => "GET",
        "callback" => "get_login_status"
    ));
    register_rest_route("v1/wol", "getdesktops", array(
        "methods" => "GET",
        "callback" => "get_desktops"
    ));
    register_rest_route("v1/wol", "getstatus", array(
        "methods" => "POST",
        "callback" => "get_status"
    ));
    register_rest_route("v1/wol", "sendwol", array(
        "methods" => "POST",
        "callback" => "send_wol"
    ));
    register_rest_route("v1/wol", "createnonce", array(
        "methods" => "POST",
        "callback" => "create_nonce"
    ));
}


function get_desktops(){
    global $desktops;
    echo json_encode($desktops);
}

function get_status($data){
    global $desktops;
    $params = $data -> get_params();
    $desktop_name = $params["name"];
    $ip = $desktops[$desktop_name]["ip"];
    $icmp_packet = pack("H*", "0800F7FF00000000");
    $socket = socket_create(AF_INET, SOCK_RAW, getprotobyname("icmp"));
    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 1.5, 'usec' => 0));
    socket_connect($socket, $ip, 0);
    socket_send($socket, $icmp_packet, strlen($icmp_packet), 0);
    if (socket_read($socket, 255)){
        echo "online";
    }
    else {
        echo "offline";
    }
    socket_close($socket);
}

function get_status_old($data){
    global $desktops;
    $params = $data -> get_params();
    $desktop_name = $params["name"];
    $ip = $desktops[$desktop_name]["ip"];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://" . $ip);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_exec($ch);
    if (curl_errno($ch)){
        if (curl_errno($ch) == 7){
            echo "online";
        }
        else {
            echo "offline";
        }
    }
    else {
        echo "online";
    }
}


function send_wol($data){
    global $desktops;
    global $current_user_id;
    wp_set_current_user($current_user_id);
    $params = $data -> get_params();
    $desktop_name = $params["name"];
    $nonce = $params["nonce"];
    if (wp_verify_nonce($nonce, "wol_nonce") && $current_user_id !== 0){
        $mac = str_replace([":", "-"], "", $desktops[$desktop_name]["mac"]);
        $mac_hex = pack("H*", $mac);
        $magic_packet = str_repeat(chr(0xFF), 6) . str_repeat($mac_hex, 16);
        $socket = socket_create(AF_INET, SOCK_DGRAM, getprotobyname("udp"));
        socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, 1);
        $magic_packet_sent = socket_sendto($socket, $magic_packet, strlen($magic_packet), 0, "255.255.255.255", 9);
        socket_close($socket);
        if ($magic_packet_sent){
            echo "sent";
        } 
        else {
            echo "error";
        }
    }
    else {
        echo "nonce_error";
    }

}


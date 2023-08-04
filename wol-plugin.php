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


if (!defined("ABSPATH")){
    die("404 Not Found");
}



class DesktopStatuses {
    public function __construct(){
        add_shortcode("statuses", "show_desktop_statuses");
        add_action("rest_api_init", "create_rest_endpoints");
        add_action("wp_enqueue_scripts", "enqueue_custom_css");

    }
}
new DesktopStatuses;



function enqueue_custom_css(){
    wp_enqueue_style("status_display", plugin_dir_url(__FILE__) . "/static/style.css", array(), null);
}


function show_desktop_statuses(){
    include plugin_dir_path( __FILE__ ) . "static/statuses.php";
}


function create_rest_endpoints(){
    register_rest_route("v1/wol", "getloginstatus", array(
        "methods" => "GET",
        "callback" => "get_login_status"
    ));
    register_rest_route("v1/wol", "getnonce", array(
        "methods" => "GET",
        "callback" => "get_nonce"
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
}


function get_login_status(){
    $current_user = get_current_user_id();
    echo $current_user;
    if (is_user_logged_in()){
        echo "1";
    }
    else {
        echo "2";
    }
}



function get_nonce(){
    $nonce = wp_create_nonce("wol_nonce");
    echo $nonce;
}


$desktops = array(
    "Example" => array(
        "ip" => "192.168.1.1",
        "mac" => "AA-BB-CC-DD-EE-FF",
    ),
    "Example2" => array(
        "ip" => "192.168.1.69",
        "mac" => "AA-BB-CC-DD-EE-FF",
    ),
    "Example3" => array(
        "ip" => "192.168.1.75",
        "mac" => "AA-BB-CC-DD-EE-FF",
    ),
    "Example4" => array(
        "ip" => "192.168.1.70",
        "mac" => "AA-BB-CC-DD-EE-FF",
    ),
);


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
    $params = $data -> get_params();
    $desktop_name = $params["name"];
    $nonce = $params["nonce"];
    if (wp_verify_nonce($nonce, "wol_nonce") and $current_user->ID != 0){
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


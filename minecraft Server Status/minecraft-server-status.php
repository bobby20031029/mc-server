<?php
/**
 * Plugin Name: Minecraft Server Status
 * Plugin URI: https://worthbuy.com.au
 * Description: 抓取 Minecraft 服务器 IP、状态、Mod 列表和在线玩家信息
 * Version: 1.0
 * Author: Ziyang Song
 * Author URI: https://worthbuy.com.au
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit;
}

// 插件激活时创建数据库表
function mss_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'minecraft_server_status';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        server_ip varchar(255) NOT NULL,
        status varchar(50) NOT NULL,
        online_players int NOT NULL,
        max_players int NOT NULL,
        mods_list text NOT NULL,
        timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'mss_create_table');

// 从 mcstatus.io 获取 Minecraft 服务器状态
function mss_fetch_server_status($server_ip) {
    $url = "https://api.mcstatus.io/v2/status/java/" . urlencode($server_ip);
    $response = wp_remote_get($url);
    
    if (is_wp_error($response)) {
        return ['status' => 'offline', 'online_players' => 0, 'max_players' => 0, 'mods_list' => 'N/A'];
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (isset($data['online']) && $data['online'] === true) {
        $mods_list = isset($data['mods']) ? implode(", ", array_column($data['mods'], 'name')) : 'Vanilla';
        return [
            'status' => 'online',
            'online_players' => $data['players']['online'] ?? 0,
            'max_players' => $data['players']['max'] ?? 0,
            'mods_list' => $mods_list
        ];
    }
    return ['status' => 'offline', 'online_players' => 0, 'max_players' => 0, 'mods_list' => 'N/A'];
}

// 短代码 [minecraft_server ip="服务器IP"]
function mss_server_shortcode($atts) {
    $atts = shortcode_atts(['ip' => ''], $atts, 'minecraft_server');
    if (empty($atts['ip'])) return '<p>请提供服务器 IP。</p>';
    
    $server_data = mss_fetch_server_status($atts['ip']);
    return "<h2>Minecraft 服务器状态</h2>
            <p><strong>服务器 IP：</strong> {$atts['ip']}</p>
            <p><strong>状态：</strong> " . esc_html($server_data['status']) . "</p>
            <p><strong>在线玩家：</strong> " . esc_html($server_data['online_players']) . "/" . esc_html($server_data['max_players']) . "</p>
            <p><strong>安装的 Mod：</strong> " . esc_html($server_data['mods_list']) . "</p>";
}
add_shortcode('minecraft_server', 'mss_server_shortcode');
?>

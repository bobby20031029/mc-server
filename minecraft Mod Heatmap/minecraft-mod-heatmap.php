<?php
/**
 * Plugin Name: Minecraft Mod Heatmap
 * Plugin URI: https://worthbuy.com.au
 * Description: 显示我的世界 Mod 玩家数量的热力图，并抓取 CurseForge 数据到本地并在网页上显示
 * Version: 2.0
 * Author: Ziyang Song
 * Author URI: https://worthbuy.com.au
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit;
}

// CurseForge API Key（请替换为你的真实 API Key）
define('$2a$10$KXc2njASUX/4geTVZmjb4Ox6czGQVAKXUcQcSATtUqGnuchFJwSf2', '你的API密钥');

// 格式化下载次数（转换为 M/K 单位）
function format_downloads($num) {
    if ($num >= 1000000) {
        return round($num / 1000000, 2) . 'M';
    } elseif ($num >= 1000) {
        return round($num / 1000, 2) . 'K';
    }
    return $num;
}

// 使用 cURL 访问 CurseForge API
function fetch_curseforge_mods() {
    $url = "https://api.curseforge.com/v1/mods/search?gameId=432&sortField=2&sortOrder=desc&pageSize=10";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-Api-Key: ' . CURSEFORGE_API_KEY,
        'User-Agent: MinecraftModHeatmap/2.0'
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        error_log("cURL 请求失败，HTTP 状态码: $http_code");
        return false;
    }

    return json_decode($response, true);
}

// 备用方案：使用 WordPress `wp_remote_get()`
function fetch_curseforge_mods_wp() {
    $url = "https://api.curseforge.com/v1/mods/search?gameId=432&sortField=2&sortOrder=desc&pageSize=10";

    $response = wp_remote_get($url, [
        'timeout' => 20,
        'sslverify' => false,
        'headers' => [
            'X-Api-Key' => CURSEFORGE_API_KEY,
            'User-Agent' => 'MinecraftModHeatmap/2.0'
        ]
    ]);

    if (is_wp_error($response)) {
        error_log("wp_remote_get() 请求失败: " . $response->get_error_message());
        return false;
    }

    return json_decode(wp_remote_retrieve_body($response), true);
}

// 获取 CurseForge Mod 数据（优先使用 cURL，失败则使用 `wp_remote_get()`）
function get_top_mods() {
    $data = fetch_curseforge_mods();
    if (!$data) {
        $data = fetch_curseforge_mods_wp();
    }

    if (!$data || empty($data['data'])) {
        return false;
    }

    $mod_list = [];
    foreach ($data['data'] as $mod) {
        $mod_list[] = [
            'name' => $mod['name'],
            'downloads' => format_downloads($mod['downloadCount'])
        ];
    }

    return $mod_list;
}

// 插件管理菜单
function mmh_admin_menu() {
    add_menu_page('Mod 数据抓取', 'Mod 数据抓取', 'manage_options', 'mmh_fetch_data', 'mmh_admin_page');
}
add_action('admin_menu', 'mmh_admin_menu');

// 管理页面
function mmh_admin_page() {
    if (isset($_POST['fetch_data'])) {
        $mods = get_top_mods();
        if (!$mods) {
            echo '<div class="error"><p>无法获取数据，请稍后再试。</p></div>';
        } else {
            echo '<div class="updated"><p>数据已抓取</p></div>';
            echo '<h2>前 10 个最热门 Mod</h2>';
            echo '<pre>' . json_encode($mods, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
        }
    }
    echo '<form method="post"><input type="submit" name="fetch_data" value="抓取 Mod 数据" class="button button-primary"></form>';
}

// 短代码 [mod_heatmap] 显示 Mod 数据
function mmh_mod_heatmap_shortcode() {
    $mods = get_top_mods();
    if (!$mods) {
        return '<p>暂无数据，请稍后再试。</p>';
    }

    $output = '<h2>当前 Mod 数据</h2><ul>';
    foreach ($mods as $mod) {
        $output .= '<li><strong>' . esc_html($mod['name']) . '</strong>: ' . esc_html($mod['downloads']) . ' 次下载</li>';
    }
    $output .= '</ul>';
    return $output;
}
add_shortcode('mod_heatmap', 'mmh_mod_heatmap_shortcode');

?>

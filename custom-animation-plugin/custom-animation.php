<?php
/*
Plugin Name: Custom Animation Plugin
Plugin URI:  https://yourwebsite.com/
Description: 一个用于添加动画效果的 WordPress 插件。
Version:     1.2
Author:      Ziyang Song
Author URI:  https://yourwebsite.com/
License:     GPL2
*/

if (!defined('ABSPATH')) {
    exit; // 防止直接访问
}

// 注册 CSS 文件
function custom_animation_enqueue_styles() {
    wp_enqueue_style('custom-animation-style', plugin_dir_url(__FILE__) . 'css/animation.css', array(), '1.0.0', 'all');
}

// 注册 JS 文件
function custom_animation_enqueue_scripts() {
    wp_enqueue_script('custom-animation-script', plugin_dir_url(__FILE__) . 'js/animation.js', array('jquery'), '1.0.0', true);
}

// 将 CSS 和 JS 添加到前端页面
add_action('wp_enqueue_scripts', 'custom_animation_enqueue_styles');
add_action('wp_enqueue_scripts', 'custom_animation_enqueue_scripts');

// 添加一个短代码 [custom_animation]
function custom_animation_shortcode() {
    return '<div class="blurred-wrapper">
        <header class="main-header container">
            <a href="#" class="logo">Wallet</a>
            <a href="#" class="btn sign-up">Sign up</a>
        </header>
        <section class="hero container">
            <div class="content-wrapper">
                <h5 class="tagline">More than just a wallet</h5>
                <h1 class="title">Make paying easier by using Wallet<span>.</span></h1>
                <p class="message">Carry your cards on any device so that you can pay in-person, online, or with the app.</p>
                <a href="#" class="btn cta">Learn more</a>
            </div>
        </section>
    </div>';
}

add_shortcode('custom_animation', 'custom_animation_shortcode');
?>

<?php

/*
Plugin Name: Custom Login History
Plugin URL: https://github.com/dynojp/custom-login-history
Description: ユーザーのログイン履歴をカスタム投稿タイプとして保存するプラグイン
Version: 0.1.0
Author: 後藤隼人
Author URI: https://dyno.design/
*/

namespace CustomLoginHistory;

defined( 'ABSPATH' ) || exit;

const POST_TYPE = 'login_history';

/**
 * カスタム投稿タイプ "login_history" を登録
 */
add_action('init', function () {
  $args = [
    'public' => false,
    'show_ui' => true,
    'show_in_menu' => 'tools.php',
    'label' => __('Login History', 'custom-login-history'),
    'supports' => ['title', 'editor', 'custom-fields'],
  ];
  register_post_type(POST_TYPE, $args);
});

/**
 * ログイン時にログイン情報を保存
 */
add_action('wp_login', function ($user_login, $user) {
  $post_data = [
    'post_type' => POST_TYPE,
    'post_title' => sprintf(__('%s logged in', 'custom-login-history'), $user->user_login),
    'post_content' => sprintf(__('Date: %s', 'custom-login-history'), current_time('mysql')),
    'post_status' => 'publish',
    'meta_input' => [
      'user_id' => $user->ID,
      'user_login' => $user->user_login,
      'login_ip' => $_SERVER['REMOTE_ADDR'],
    ],
  ];

  wp_insert_post($post_data);
}, 10, 2);

/**
 * 管理画面のログイン履歴の一覧ページをカスタマイズ
 */
add_filter('manage_' . POST_TYPE . '_posts_columns', function ($columns) {
  $new_columns = [
    'cb' => $columns['cb'],
    'title' => __('Title', 'custom-login-history'),
    'user_login' => __('Username', 'custom-login-history'), 
    'login_ip' => __('IP Address', 'custom-login-history'),
    'login_date' => __('Login Date', 'custom-login-history'),
  ];
  return $new_columns;
});

/**
 * 管理画面のログイン履歴の一覧ページの各レコードの値を返す
 */
add_action('manage_' . POST_TYPE . '_posts_custom_column', function ($column_name, $post_id) {
  switch ($column_name) {
    case 'user_login':
      $user_login = get_post_meta($post_id, 'user_login', true);
      echo $user_login;
      break;
    case 'login_ip':
      $login_ip = get_post_meta($post_id, 'login_ip', true);
      echo $login_ip;  
      break;
    case 'login_date':
      echo get_the_date('Y-m-d H:i', $post_id);
      break;
  }
}, 10, 2);

<?php
/**
 * Plugin Name: DPS Login Security
 * Plugin URI: https://dps.media/
 * Description: Enhanced WordPress login security with custom login page, rate limiting, and protection against brute force attacks.
 * Version: 7.0.11
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * Author: DPS.Media
 * Author URI: https://dps.media/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: dps-login-security
 * Domain Path: /languages
 *
 * @package DPS_Login_Security
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Load plugin textdomain
// add_action('plugins_loaded', 'dps_login_security_load_textdomain'); // Removed as discouraged since WP 4.6

if (!defined('DPS_LOGIN_SECURITY_VERSION')) {
    define('DPS_LOGIN_SECURITY_VERSION', '7.0.11');
}

// Security module constants
if (!defined('DPS_SECURITY_LOG_RETENTION_DAYS')) {
    define('DPS_SECURITY_LOG_RETENTION_DAYS', 30);
}

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'caldps_activate_plugin');
register_deactivation_hook(__FILE__, 'caldps_deactivate_plugin');

function caldps_activate_plugin() {
    caldps_create_rate_limit_table();
    dps_create_security_log_table();
    dps_login_security_seed_defaults();
    update_option('caldps_needs_rewrite_flush', true);
    update_option('dps_login_security_version', DPS_LOGIN_SECURITY_VERSION);
}

function caldps_deactivate_plugin() {
    // Optionally cleanup on deactivation if the user explicitly wants to reset
    // For now, we keep the data for safety during upgrades
    flush_rewrite_rules();
}

/**
 * Ensures defaults exist and triggers database updates on version mismatch.
 */
function dps_login_security_bootstrap_defaults() {
    static $bootstrapped = false;
    if ($bootstrapped) return;
    $bootstrapped = true;

    $installed_version = get_option('dps_login_security_version');

    if ($installed_version !== DPS_LOGIN_SECURITY_VERSION) {
        caldps_activate_plugin();
    }
}

// Apply bootstrap on plugins_loaded to handle updates smoothly
add_action('plugins_loaded', 'dps_login_security_bootstrap_defaults', 1);

// Text domain is handled automatically by WordPress.org for plugins hosted there.

// === Settings Page ===
add_action('admin_menu', function() {
    add_options_page('DPS Login Security', 'Custom Admin Login', 'manage_options', 'custom-admin-login-dps', 'caldps_settings_page_v55');
});

function caldps_get_default_html() {
    return '<div class="dps-login-left">
    <div class="dps-login-content">
        <div class="dps-login-header">
            <div class="dps-login-title"><a href="https://dps.media/" target="_blank">DPS.MEDIA</a></div>
            <div class="dps-login-slogan">Marketing hiệu quả, bằng cả trái tim</div>
            <div class="dps-login-desc">Cung cấp dịch vụ tiếp thị số trọn gói dành cho doanh nghiệp vừa và nhỏ</div>
        </div>
        
        <div class="dps-login-services">
            <a href="https://dps.media/dich-vu-truyen-thong-tong-the/">Truyền Thông Tổng Thể</a>
            <a href="https://dps.media/dich-vu-thiet-ke-website/">Thiết Kế Website</a>
            <a href="https://dps.media/dich-vu-thiet-ke-do-hoa/">Thiết Kế Đồ Họa</a>
            <a href="https://dps.media/dich-vu-seo-tong-the/">SEO Tổng Thể</a>
            <a href="https://dps.media/dich-vu-e-commerce/">E-commerce</a>
            <a href="https://dps.media/dich-vu-cham-soc-fanpage/">Chăm Sóc Fanpage</a>
            <a href="https://dps.media/dich-vu-dang-ky-gov/">Đăng Ký GOV</a>
            <a href="https://dps.media/dich-vu-seeding/">Seeding</a>
            <a href="https://dps.media/dich-vu-google-maps/">Google Maps</a>
            <a href="https://dps.media/dich-vu-quang-cao/">Quảng Cáo</a>
            <a href="https://dps.media/dich-vu-content-marketing/">Content Marketing</a>
            <a href="https://dps.media/dich-vu-email-marketing/">Email Marketing</a>
            <a href="https://dps.media/dich-vu-zalo-marketing/">Zalo Marketing</a>
            <a href="https://dps.media/dich-vu-affiliate-marketing/">Affiliate Marketing</a>
            <a href="https://dps.media/dich-vu-booking-kols/">Booking KOLs</a>
        </div>
    </div>
    
    <div class="caldps-support">
        Cần hỗ trợ ngay? Gọi 0961 54 54 45<br>Đội ngũ chuyên gia tiếp thị số luôn sẵn sàng giải đáp mọi thắc mắc của bạn</b>
    </div>
</div>';
}

function caldps_get_default_css() {
    return '/* Reset cho phần tử login left */
.caldps-left-custom {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.caldps-left-custom *,
.caldps-left-custom *::before,
.caldps-left-custom *::after {
    box-sizing: border-box;
}

/* Container chính cho bên trái */
.dps-login-left {
    width: 100%;
    height: 100%;
    min-height: 600px;
    padding: 40px 30px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    background: linear-gradient(135deg, #fefffe 0%, #f8fdf8 100%);
    font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif !important;
    position: relative;
}

.dps-login-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.dps-login-header {
    text-align: center;
    margin-bottom: 35px;
}

.dps-login-title {
    font-size: 2.2rem;
    font-weight: 800;
    color: #2d5016;
    margin-bottom: 10px;
    letter-spacing: -1.2px;
    background: linear-gradient(135deg, #2d5016 0%, #4a7c59 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-align: center;
}

.dps-login-slogan {
    font-size: 1.15rem;
    font-weight: 600;
    color: #1f783d;
    margin-bottom: 6px;
    line-height: 1.4;
    text-align: center;
}

.dps-login-desc {
    font-size: 1rem;
    color: #666;
    font-style: italic;
    opacity: 0.9;
    text-align: center;
}

.dps-login-services {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 12px;
    margin-top: 25px;
    width: 100%;
}

.dps-login-services a {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    color: #2563eb;
    text-decoration: none;
    font-weight: 600;
    border-radius: 10px;
    padding: 14px 10px;
    font-size: 0.9rem;
    text-align: center;
    box-shadow: 0 3px 10px rgba(46, 125, 50, 0.08);
    border: 2px solid #f0f9f0;
    transition: all 0.25s ease;
    min-height: 50px;
    position: relative;
    overflow: hidden;
}

.dps-login-services a::before {
    content: \'\';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(46, 125, 50, 0.1), transparent);
    transition: left 0.4s ease;
}

.dps-login-services a:hover {
    background: linear-gradient(135deg, #f0fff4 0%, #e8f5e8 100%);
    color: #1565c0;
    box-shadow: 0 6px 20px rgba(46, 125, 50, 0.12);
    border-color: #c8e6c9;
    transform: translateY(-1px);
}

.dps-login-services a:hover::before {
    left: 100%;
}

.caldps-support {
    background: linear-gradient(135deg, #e8f5e8 0%, #f1f8e9 100%);
    padding: 18px;
    border-radius: 12px;
    font-size: 0.95rem;
    color: #2e7d32;
    font-weight: 500;
    text-align: center;
    border: 1px solid #c8e6c9;
    margin-top: 25px;
    line-height: 1.4;
}

.caldps-support b {
    color: #1b5e20;
    font-size: 1.05rem;
}

/* CSS cho phần bên phải - Form đăng nhập */
.caldps-right {
    width: 50%;
    min-width: 340px;
    min-height: 600px;
    box-sizing: border-box;
    padding: 50px 40px 38px 40px;
    background: #fff;
    font-family: \'Segoe UI\', Arial, Tahoma, Geneva, Verdana, sans-serif !important;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.caldps-right .dps-login-logo {
    margin-bottom: 22px;
    max-width: 100px;
    margin-left: auto;
    margin-right: auto;
    display: block;
}

.caldps-right .dps-login-greeting {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f783d;
    margin-bottom: 20px;
    margin-top: 0;
    letter-spacing: -0.5px;
    text-align: center;
}

.caldps-right form {
    width: 100%;
    max-width: 370px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 18px;
    align-items: center;
}

.caldps-right input[type="text"],
.caldps-right input[type="password"] {
    width: 100%;
    padding: 13px 13px;
    border-radius: 9px;
    border: 1.7px solid #e2f3e2;
    background: #f8fdf8;
    font-size: 1.07rem;
    margin-bottom: 3px;
    box-sizing: border-box;
    transition: border 0.18s;
}

.caldps-right input[type="text"]:focus,
.caldps-right input[type="password"]:focus {
    border-color: #30b95c;
    background: #f1fff6;
}

.caldps-right .rememberme {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    font-size: 1.01rem;
    color: #222;
    margin-bottom: 12px;
    margin-top: 8px;
    width: 100%;
}

.caldps-right input[type="checkbox"] {
    accent-color: #30b95c;
    width: 17px;
    height: 17px;
}

.caldps-right input[type="submit"] {
    width: 100%;
    border: none;
    border-radius: 10px;
    background: linear-gradient(90deg, #30b95c 40%, #54b547 100%);
    color: #fff;
    font-weight: 700;
    font-size: 1.16rem;
    padding: 14px 0 13px 0;
    box-shadow: 0 2px 18px #53d04724;
    margin-bottom: 0;
    cursor: pointer;
    transition: background 0.17s, box-shadow 0.18s;
}

.caldps-right input[type="submit"]:hover {
    background: linear-gradient(90deg, #198d3c 0%, #3eae33 100%);
    box-shadow: 0 4px 24px #53d0472e;
}

.caldps-right .forgot-password,
.caldps-right a {
    color: #2e7d32;
    font-size: 1.04rem;
    text-decoration: none;
    font-weight: 600;
    margin-top: 14px;
    display: inline-block;
    transition: color .16s;
}

.caldps-right .forgot-password:hover,
.caldps-right a:hover {
    color: #1b5e20;
    text-decoration: underline;
}

/* Đảm bảo mọi thứ luôn căn giữa */
.caldps-right > * {
    margin-left: auto;
    margin-right: auto;
}

/* Responsive cho mobile */
@media (max-width: 768px) {
    .dps-login-left {
        padding: 25px 20px;
        min-height: 500px;
    }

    .dps-login-title {
        font-size: 1.8rem;
    }

    .dps-login-slogan {
        font-size: 1rem;
    }

    .dps-login-desc {
        font-size: 0.9rem;
    }

    .dps-login-services {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .dps-login-services a {
        font-size: 0.85rem;
        padding: 12px 8px;
        min-height: 45px;
    }

    .caldps-support {
        font-size: 0.9rem;
        padding: 15px;
    }

    .caldps-right {
        padding: 32px 12px 22px 12px;
        min-width: unset;
        min-height: 450px;
    }
    .caldps-right form { max-width: 98vw; }
}

@media (max-width: 480px) {
    .dps-login-left {
        padding: 20px 15px;
    }

    .dps-login-title {
        font-size: 1.6rem;
    }

    .dps-login-services {
        grid-template-columns: 1fr;
        gap: 8px;
    }

    .dps-login-services a {
        font-size: 0.8rem;
        padding: 10px 6px;
        min-height: 40px;
    }

    .caldps-right {
        width: 100%;
        padding: 18px 2vw;
    }
    .caldps-right form { max-width: 99vw; }
}

/* Animation nhẹ nhàng */
.dps-login-services {
    animation: fadeInUp 0.5s ease-out;
}

.dps-login-services a:nth-child(odd) {
    animation: slideInLeft 0.4s ease-out;
}

.dps-login-services a:nth-child(even) {
    animation: slideInRight 0.4s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-15px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(15px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}';
}

function dps_login_security_seed_defaults() {
    $defaults = array(
        'caldps_slug' => 'admindps',
        'caldps_greeting' => 'Chào mừng admin quay lại!',
        'caldps_logo' => '',
        'caldps_disable_file_edit' => 1,
        'caldps_disable_error_reporting' => 1,
        'caldps_disable_pingbacks' => 1,
        'caldps_hide_wp_version' => 1,
        'caldps_hide_plugin_version' => 1,
        'caldps_enable_rate_limit' => 1,
        'caldps_rate_limit_attempts' => 5,
        'caldps_rate_limit_time' => 15,
        'caldps_rate_limit_block_time' => 60,
        // v7.0 Advanced Protection (enabled by default for high priority features)
        'dps_block_xmlrpc' => 1,
        'dps_detect_login_scan' => 1,
        'dps_block_user_enum' => 1,
        // Optional features (disabled by default)
        'dps_block_bad_agents' => 0,
        'dps_verify_googlebot' => 0,
        'dps_block_bad_methods' => 0,
        'dps_validate_query_strings' => 0,
        'dps_block_sensitive_files' => 0,
        'dps_block_standard_login' => 0,
    );

    foreach ($defaults as $option => $value) {
        if (false === get_option($option, false)) {
            update_option($option, $value);
        }
    }
}

function caldps_settings_page_v55() {
    // Handle clearing a specific blocked IP
    if (isset($_POST['caldps_clear_ip'])) {
        check_admin_referer('caldps_clear_ip');
        $ip_to_clear = isset($_POST['caldps_ip_to_clear']) ? trim(sanitize_text_field(wp_unslash($_POST['caldps_ip_to_clear']))) : '';

        if (!$ip_to_clear || !filter_var($ip_to_clear, FILTER_VALIDATE_IP)) {
            echo '<div class="error"><p>IP không hợp lệ. Vui lòng nhập IPv4/IPv6 hợp lệ.</p></div>';
        } else {
            global $wpdb;
            $table_name = $wpdb->prefix . 'dps_rate_limit';
            $deleted = $wpdb->delete($table_name, array('ip_address' => $ip_to_clear));
            if ($deleted) {
                echo '<div class="updated"><p>✅ Đã xóa giới hạn/khóa cho IP: <code>' . esc_html($ip_to_clear) . '</code></p></div>';
            } else {
                echo '<div class="notice notice-warning"><p>⚠️ Không tìm thấy bản ghi rate limit cho IP: <code>' . esc_html($ip_to_clear) . '</code></p></div>';
            }
        }
    }

    if (isset($_POST['caldps_save'])) {
        check_admin_referer('caldps_save_settings');
        $old_slug = get_option('caldps_slug', 'admindps');
        $new_slug = sanitize_title(isset($_POST['caldps_slug']) ? wp_unslash($_POST['caldps_slug']) : 'admindps');

        // Validate slug - avoid conflicts
        $forbidden_slugs = array(
            'wp-login', 'wp-login.php', 'wp-admin', 'admin', 'login', 'wp-content', 'wp-includes', 'wp-json',
            'admin-ajax', 'admin-ajax.php', 'admin-post', 'admin-post.php', 'async-upload', 'async-upload.php',
            'xmlrpc', 'xmlrpc.php', 'wp-cron', 'wp-cron.php', 'wp-signup', 'wp-signup.php', 'upgrade', 'install'
        );
        if (in_array($new_slug, $forbidden_slugs)) {
            echo '<div class="error"><p>Slug không được phép. Vui lòng chọn slug khác.</p></div>';
            return;
        }

        // Check if page/post with this slug exists
        if (get_page_by_path($new_slug)) {
            echo '<div class="error"><p>Đã tồn tại trang với slug này. Vui lòng chọn slug khác.</p></div>';
            return;
        }

        update_option('caldps_slug', $new_slug);
        update_option('caldps_greeting', sanitize_text_field(isset($_POST['caldps_greeting']) ? wp_unslash($_POST['caldps_greeting']) : ''));
        update_option('caldps_logo', esc_url_raw(isset($_POST['caldps_logo']) ? wp_unslash($_POST['caldps_logo']) : ''));
        
        // Sanitize custom HTML and CSS
        update_option('caldps_left_custom_html', wp_kses_post(isset($_POST['caldps_left_custom_html']) ? wp_unslash($_POST['caldps_left_custom_html']) : ''));
        update_option('caldps_left_custom_css', wp_strip_all_tags(isset($_POST['caldps_left_custom_css']) ? wp_unslash($_POST['caldps_left_custom_css']) : ''));
        
        // Lưu các tùy chọn bảo mật
        update_option('caldps_disable_file_edit', isset($_POST['caldps_disable_file_edit']) ? 1 : 0);
        update_option('caldps_disable_error_reporting', isset($_POST['caldps_disable_error_reporting']) ? 1 : 0);
        update_option('caldps_disable_pingbacks', isset($_POST['caldps_disable_pingbacks']) ? 1 : 0);
        update_option('caldps_hide_wp_version', isset($_POST['caldps_hide_wp_version']) ? 1 : 0);
        update_option('caldps_hide_plugin_version', isset($_POST['caldps_hide_plugin_version']) ? 1 : 0);

        // Lưu cai đặt rate limiting
        update_option('caldps_enable_rate_limit', isset($_POST['caldps_enable_rate_limit']) ? 1 : 0);
        update_option('caldps_rate_limit_attempts', absint(isset($_POST['caldps_rate_limit_attempts']) ? wp_unslash($_POST['caldps_rate_limit_attempts']) : 5));
        update_option('caldps_rate_limit_time', absint(isset($_POST['caldps_rate_limit_time']) ? wp_unslash($_POST['caldps_rate_limit_time']) : 15));
        update_option('caldps_rate_limit_block_time', absint(isset($_POST['caldps_rate_limit_block_time']) ? wp_unslash($_POST['caldps_rate_limit_block_time']) : 60));
        
        // Luu cài đặt Advanced Protection (v7.0)
        update_option('dps_block_xmlrpc', isset($_POST['dps_block_xmlrpc']) ? 1 : 0);
        update_option('dps_detect_login_scan', isset($_POST['dps_detect_login_scan']) ? 1 : 0);
        update_option('dps_block_user_enum', isset($_POST['dps_block_user_enum']) ? 1 : 0);
        update_option('dps_block_bad_agents', isset($_POST['dps_block_bad_agents']) ? 1 : 0);
        update_option('dps_verify_googlebot', isset($_POST['dps_verify_googlebot']) ? 1 : 0);
        update_option('dps_block_bad_methods', isset($_POST['dps_block_bad_methods']) ? 1 : 0);
        update_option('dps_validate_query_strings', isset($_POST['dps_validate_query_strings']) ? 1 : 0);
        update_option('dps_block_standard_login', isset($_POST['dps_block_standard_login']) ? 1 : 0);
        update_option('dps_block_sensitive_files', isset($_POST['dps_block_sensitive_files']) ? 1 : 0);
        
        if ($old_slug !== $new_slug) {
            // Schedule rewrite rules flush for next init
            update_option('caldps_needs_rewrite_flush', true);
        }
        
        // Tạo/cập nhật .htaccess nếu cần
        caldps_update_htaccess();

        // Show success message with current login URL
        $current_slug = get_option('caldps_slug', 'admindps');
        $login_url = home_url("/$current_slug/");
        echo '<div class="updated"><p>✅ Đã lưu cài đặt bảo mật! <br><strong>URL đăng nhập mới:</strong> <a href="' . esc_url($login_url) . '">' . esc_html($login_url) . '</a></p></div>';
    }
    
    $slug = get_option('caldps_slug', 'admindps');
    $greeting = get_option('caldps_greeting', 'Chào mừng admin quay lại!');
    $logo = get_option('caldps_logo', '');
    
    // Lấy giá trị hiện tại, nếu trống thì dùng mặc định
    $left_custom_html = get_option('caldps_left_custom_html', '');
    if (empty($left_custom_html)) {
        $left_custom_html = caldps_get_default_html();
    }
    $left_custom_css = get_option('caldps_left_custom_css', '');
    if (empty($left_custom_css)) {
        $left_custom_css = caldps_get_default_css();
    }
    
    // Variable preparation for template. 
    // Redundant esc_textarea() removed here to prevent double-escaping 
    // since escaping is already applied in the HTML output below.
    $left_custom_html = $left_custom_html;
    $left_custom_css = $left_custom_css;
    
    // Lấy các tùy chọn bảo mật
    $disable_file_edit = get_option('caldps_disable_file_edit', 0);
    $disable_error_reporting = get_option('caldps_disable_error_reporting', 0);
    $disable_pingbacks = get_option('caldps_disable_pingbacks', 0);
    $hide_wp_version = get_option('caldps_hide_wp_version', 0);
    $hide_plugin_version = get_option('caldps_hide_plugin_version', 0);

    // Lấy cài đặt rate limiting
    $enable_rate_limit = get_option('caldps_enable_rate_limit', 0);
    $rate_limit_attempts = get_option('caldps_rate_limit_attempts', 5);
    $rate_limit_time = get_option('caldps_rate_limit_time', 15);
    $rate_limit_block_time = get_option('caldps_rate_limit_block_time', 60);
    
    // Lấy cài đặt Advanced Protection (v7.0)
    $block_xmlrpc = get_option('dps_block_xmlrpc', 1);
    $detect_login_scan = get_option('dps_detect_login_scan', 1);
    $block_user_enum = get_option('dps_block_user_enum', 1);
    $block_bad_agents = get_option('dps_block_bad_agents', 0);
    $verify_googlebot = get_option('dps_verify_googlebot', 0);
    $block_bad_methods = get_option('dps_block_bad_methods', 0);
    $validate_query_strings = get_option('dps_validate_query_strings', 0);
    $block_sensitive_files = get_option('dps_block_sensitive_files', 0);
    
    // Lấy danh sách IP đang bị chặn (nếu có) để hiển thị
    global $wpdb;
    $blocked_list = array();
    $all_attempts = array();
    if ($enable_rate_limit) {
        $table_name = $wpdb->prefix . 'dps_rate_limit';
        $now = current_time('mysql');
        $blocked_list = $wpdb->get_results($wpdb->prepare(
            "SELECT ip_address, attempt_count, blocked_until FROM {$table_name} WHERE is_blocked = 1 AND blocked_until > %s ORDER BY blocked_until DESC LIMIT 50",
            $now
        ));
        
        // Get all recent login attempts (blocked and not blocked) for monitoring
        $all_attempts = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ip_address, attempt_count, last_attempt, is_blocked, blocked_until 
                 FROM {$table_name} 
                 ORDER BY last_attempt DESC 
                 LIMIT %d",
                100
            )
        );
    }
    ?>
    
    <style>
    .caldps-security-section {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        margin: 20px 0;
        padding: 20px;
    }
    .caldps-security-section h3 {
        margin-top: 0;
        color: #23282d;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }
    .caldps-checkbox-item {
        display: flex;
        align-items: center;
        margin: 15px 0;
        padding: 10px;
        background: #f9f9f9;
        border-radius: 4px;
    }
    .caldps-checkbox-item input[type="checkbox"] {
        margin-right: 10px;
        transform: scale(1.2);
    }
    .caldps-checkbox-item label {
        font-weight: 600;
        color: #23282d;
        margin-right: 10px;
    }
    .caldps-checkbox-item .description {
        color: #666;
        font-style: italic;
        font-size: 0.9em;
    }
    .caldps-warning {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 4px;
        padding: 10px;
        margin: 10px 0;
        color: #856404;
    }
    </style>
    
    <div class="wrap">
        <h1>Cài đặt DPS Login Security v7.0.11</h1>
        
        <form method="post">
            <?php wp_nonce_field('caldps_save_settings'); ?>
            
            <!-- Phần cài đặt login -->
            <div class="caldps-security-section">
                <h3>🔐 Cài đặt trang đăng nhập</h3>
                <table class="form-table">
                    <tr><th>Slug trang login</th>
                        <td><input name="caldps_slug" value="<?php echo esc_attr($slug); ?>" required> <small>VD: admindps</small></td></tr>
                    <tr><th>Greeting (dòng chào mừng)</th>
                        <td><input name="caldps_greeting" value="<?php echo esc_attr($greeting); ?>" style="width:350px"></td></tr>
                    <tr><th>Logo URL (PNG/SVG/JPG)</th>
                        <td><input name="caldps_logo" value="<?php echo esc_url($logo); ?>" style="width:350px"></td></tr>
                    <tr><th>HTML tự do cho bên trái</th>
                        <td><textarea name="caldps_left_custom_html" style="width:550px;height:220px;"><?php echo esc_textarea($left_custom_html); ?></textarea><br>
                        <small>Toàn bộ phần trái sẽ hiển thị đúng HTML này. Để trống sẽ sử dụng HTML mặc định của DPS.MEDIA.</small></td></tr>
                    <tr><th>CSS tự do cho bên trái</th>
                        <td><textarea name="caldps_left_custom_css" style="width:550px;height:120px;"><?php echo esc_textarea($left_custom_css); ?></textarea>
                        <br><small>Inject vào &lt;style&gt;. Để trống sẽ sử dụng CSS mặc định.</small></td></tr>
                    <tr><th>Chặn truy cập mặc định</th>
                        <td><label><input type="checkbox" name="dps_block_standard_login" value="1" <?php checked(get_option('dps_block_standard_login', 0), 1); ?>> 
                        <strong>Chặn truy cập vào wp-login.php và /wp-admin</strong></label>
                        <br><small>Nếu bật, người dùng truy cập đường dẫn mặc định sẽ bị lỗi 403 Forbidden thay vì được chuyển hướng tới trang đăng nhập mới. Giúp giấu trang đăng nhập tốt hơn.</small></td></tr>
                </table>
            </div>
            
            <!-- Phần bảo mật nâng cao -->
            <div class="caldps-security-section">
                <h3>🛡️ Tính năng bảo mật nâng cao</h3>
                <div class="caldps-warning">
                    <strong>ℹ️ Lưu ý:</strong> Một số tính năng bảo mật (XML-RPC, file execution, directory browsing, wp-login.php blocking) đã được xử lý ở Nginx server level hiệu quả hơn.
                </div>

                <h4>🚫 Tắt các tính năng nguy hiểm</h4>
                
                <div class="caldps-checkbox-item">
                    <input type="checkbox" id="caldps_disable_file_edit" name="caldps_disable_file_edit" value="1" <?php checked($disable_file_edit, 1); ?>>
                    <label for="caldps_disable_file_edit">Tắt chỉnh sửa file trong admin</label>
                    <span class="description">Vô hiệu hóa việc edit plugin/theme từ WordPress admin</span>
                </div>
                
                  
                <div class="caldps-checkbox-item">
                    <input type="checkbox" id="caldps_disable_error_reporting" name="caldps_disable_error_reporting" value="1" <?php checked($disable_error_reporting, 1); ?>>
                    <label for="caldps_disable_error_reporting">Tắt hiển thị lỗi PHP</label>
                    <span class="description">Ẩn thông báo lỗi PHP khỏi người dùng (khuyến nghị cho production)</span>
                </div>
                
                <div class="caldps-checkbox-item">
                    <input type="checkbox" id="caldps_disable_pingbacks" name="caldps_disable_pingbacks" value="1" <?php checked($disable_pingbacks, 1); ?>>
                    <label for="caldps_disable_pingbacks">Tắt pingbacks & trackbacks</label>
                    <span class="description">Ngăn chặn spam từ pingback và trackback</span>
                </div>
                
                <h4>🙈 Ẩn thông tin nhạy cảm</h4>
                
                <div class="caldps-checkbox-item">
                    <input type="checkbox" id="caldps_hide_wp_version" name="caldps_hide_wp_version" value="1" <?php checked($hide_wp_version, 1); ?>>
                    <label for="caldps_hide_wp_version">Ẩn phiên bản WordPress</label>
                    <span class="description">Loại bỏ thông tin version WordPress khỏi HTML và RSS</span>
                </div>
                
                <div class="caldps-checkbox-item">
                    <input type="checkbox" id="caldps_hide_plugin_version" name="caldps_hide_plugin_version" value="1" <?php checked($hide_plugin_version, 1); ?>>
                    <label for="caldps_hide_plugin_version">Ẩn phiên bản plugin/theme</label>
                    <span class="description">Loại bỏ thông tin version từ CSS/JS files</span>
                </div>
            </div>

            <!-- Phần Rate Limiting -->
            <div class="caldps-security-section">
                <h3>🚦 Rate Limiting - Chống Brute Force</h3>
                <div class="caldps-warning">
                    <strong>ℹ️ Lưu ý:</strong> Rate limiting giúp bảo vệ trang đăng nhập và các đường dẫn admin khỏi brute force attacks.
                </div>

                <div class="caldps-checkbox-item">
                    <input type="checkbox" id="caldps_enable_rate_limit" name="caldps_enable_rate_limit" value="1" <?php checked($enable_rate_limit, 1); ?>>
                    <label for="caldps_enable_rate_limit">Bật Rate Limiting</label>
                    <span class="description">Chống brute force attack vào trang đăng nhập và admin URLs</span>
                </div>

                <table class="form-table" id="caldps_rate_limit_settings" style="<?php echo $enable_rate_limit ? '' : 'display:none;'; ?>">
                    <tr>
                        <th>Số lần thử tối đa</th>
                        <td>
                            <input type="number" name="caldps_rate_limit_attempts" value="<?php echo esc_attr($rate_limit_attempts); ?>" min="1" max="20" required>
                            <small>Số lần đăng nhập thất bại tối đa cho phép</small>
                        </td>
                    </tr>
                    <tr>
                        <th>Thời gian giới hạn (phút)</th>
                        <td>
                            <input type="number" name="caldps_rate_limit_time" value="<?php echo esc_attr($rate_limit_time); ?>" min="1" max="60" required>
                            <small>Trong khoảng thời gian bao nhiêu phút</small>
                        </td>
                    </tr>
                    <tr>
                        <th>Thời gian chặn (phút)</th>
                        <td>
                            <input type="number" name="caldps_rate_limit_block_time" value="<?php echo esc_attr($rate_limit_block_time); ?>" min="1" max="1440" required>
                            <small>Thời gian chặn IP nếu vượt quá giới hạn</small>
                        </td>
                    </tr>
                </table>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const checkbox = document.getElementById('caldps_enable_rate_limit');
                    const settings = document.getElementById('caldps_rate_limit_settings');

                    if (checkbox && settings) {
                        checkbox.addEventListener('change', function() {
                            settings.style.display = this.checked ? '' : 'none';
                        });
                    }
                });
                </script>

            </div>
            
            <!-- Phần Advanced Protection (v7.0) -->
            <div class="caldps-security-section">
                <h3>🛡️ Advanced Protection (v7.0 - NEW!)</h3>
                <div class="caldps-warning">
                    <strong>ℹ️ Lưu ý:</strong> Các tính năng mới được thiết kế để chặn các cuộc tấn công tự động. XMLRPC và Login Scan được bật mặc định.
                </div>

                <h4>🔥 Bảo vệ ưu tiên cao (Khuyến nghị BẬT)</h4>
                
                <div class="caldps-checkbox-item" style="background: #fffaf0; border-left: 4px solid #ff6b6b;">
                    <input type="checkbox" id="dps_block_xmlrpc" name="dps_block_xmlrpc" value="1" <?php checked($block_xmlrpc, 1); ?>>
                    <label for="dps_block_xmlrpc">🚫 Chặn XMLRPC hoàn toàn</label>
                    <span class="description">Chặn tất cả XML-RPC requests (bao gồm pingback attacks). <strong>Rất khuyến nghị!</strong></span>
                </div>
                
                <div class="caldps-checkbox-item" style="background: #fffaf0; border-left: 4px solid #ff6b6b;">
                    <input type="checkbox" id="dps_detect_login_scan" name="dps_detect_login_scan" value="1" <?php checked($detect_login_scan, 1); ?>>
                    <label for="dps_detect_login_scan">🔍 Phát hiện Login Scan</label>
                    <span class="description">Ghi log các lần đăng nhập thất bại với username phổ biến (admin, root, etc). <strong>Khuyến nghị!</strong></span>
                </div>
                
                <div class="caldps-checkbox-item" style="background: #fffaf0; border-left: 4px solid #ff6b6b;">
                    <input type="checkbox" id="dps_block_user_enum" name="dps_block_user_enum" value="1" <?php checked($block_user_enum, 1); ?>>
                    <label for="dps_block_user_enum">🙈 Chặn Username Enumeration</label>
                    <span class="description">Chặn scan username qua author pages (?author=N). <strong>Khuyến nghị!</strong></span>
                </div>

                <h4>🤖 Bảo vệ nâng cao (Tùy chọn)</h4>
                
                <div class="caldps-checkbox-item">
                    <input type="checkbox" id="dps_block_bad_agents" name="dps_block_bad_agents" value="1" <?php checked($block_bad_agents, 1); ?>>
                    <label for="dps_block_bad_agents">🤖 Chặn Bad User Agents</label>
                    <span class="description">Chặn bots xấu (sqlmap, nikto, Bytespider, AhrefsBot, SemrushBot, etc)</span>
                </div>
                
                <div class="caldps-checkbox-item">
                    <input type="checkbox" id="dps_verify_googlebot" name="dps_verify_googlebot" value="1" <?php checked($verify_googlebot, 1); ?>>
                    <label for="dps_verify_googlebot">🕷️ Xác thực Googlebot</label>
                    <span class="description">Chặn fake Googlebot qua reverse DNS check</span>
                </div>
                
                <div class="caldps-checkbox-item">
                    <input type="checkbox" id="dps_block_bad_methods" name="dps_block_bad_methods" value="1" <?php checked($block_bad_methods, 1); ?>>
                    <label for="dps_block_bad_methods">🚫 Chặn HTTP Methods nguy hiểm</label>
                    <span class="description">Chặn TRACE, TRACK, CONNECT, DELETE, PATCH methods</span>
                </div>
                
                <div class="caldps-checkbox-item">
                    <input type="checkbox" id="dps_validate_query_strings" name="dps_validate_query_strings" value="1" <?php checked($validate_query_strings, 1); ?>>
                    <label for="dps_validate_query_strings">🔍 Kiểm tra Query Strings</label>
                    <span class="description">Chặn SQLi, LFI, XSS, Code Injection trong URL parameters</span>
                </div>
                
                <div class="caldps-checkbox-item">
                    <input type="checkbox" id="dps_block_sensitive_files" name="dps_block_sensitive_files" value="1" <?php checked($block_sensitive_files, 1); ?>>
                    <label for="dps_block_sensitive_files">📁 Chặn truy cập file nhạy cảm</label>
                    <span class="description">Chặn wp-config.php, .env, .git, .sql, .bak, readme.html, etc</span>
                </div>
            </div>
            
            <p><button class="button-primary" name="caldps_save" value="1">💾 Lưu tất cả cài đặt</button></p>
        </form>
        <?php if ($enable_rate_limit) : ?>
        <div class="caldps-security-section caldps-clear-ip" style="margin-top: 0;">
            <h3>🧹 Gỡ chặn IP cụ thể</h3>
            <p>Nhập IP đang bị chặn để xóa block/counters ngay lập tức.</p>
            <form method="post" style="display:flex; gap:10px; align-items:center;">
                <?php wp_nonce_field('caldps_clear_ip'); ?>
                <input type="text" name="caldps_ip_to_clear" placeholder="Ví dụ: 203.0.113.5" style="min-width:260px" />
                <button class="button" name="caldps_clear_ip" value="1">Gỡ chặn IP</button>
            </form>

            <?php if (!empty($blocked_list)) : ?>
                <h4 style="margin-top:18px;">Danh sách IP đang bị chặn</h4>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>IP</th>
                            <th>Số lần thử</th>
                            <th>Chặn đến</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($blocked_list as $row): ?>
                            <tr>
                                <td><code><?php echo esc_html($row->ip_address); ?></code></td>
                                <td><?php echo intval($row->attempt_count); ?></td>
                                <td><?php echo esc_html($row->blocked_until); ?></td>
                                <td>
                                    <form method="post" style="margin:0;">
                                        <?php wp_nonce_field('caldps_clear_ip'); ?>
                                        <input type="hidden" name="caldps_ip_to_clear" value="<?php echo esc_attr($row->ip_address); ?>" />
                                        <button class="button" name="caldps_clear_ip" value="1">Gỡ chặn</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><em>Hiện không có IP nào bị chặn.</em></p>
            <?php endif; ?>
            
            <?php if (!empty($all_attempts)) : ?>
                <h4 style="margin-top:28px;">📊 Monitor Login Spam - Tất cả các lần thử đăng nhập</h4>
                <p style="margin-bottom: 12px;"><em>Bảng này hiển thị tất cả các IP đã thử đăng nhập, giúp bạn phát hiện spam patterns sớm.</em></p>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>IP Address</th>
                            <th>Số lần thử</th>
                            <th>Lần thử cuối</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_attempts as $row): 
                            $is_currently_blocked = ((int)$row->is_blocked === 1 && !empty($row->blocked_until) && strtotime($row->blocked_until) > current_time('timestamp'));
                            $status_class = $is_currently_blocked ? 'style="background-color: #ffc9c9;"' : '';
                            $status_text = $is_currently_blocked ? '🚫 Đang bị chặn' : '✅ Đang theo dõi';
                        ?>
                            <tr <?php echo esc_attr($status_class); ?>>
                                <td><code><?php echo esc_html($row->ip_address); ?></code></td>
                                <td>
                                    <strong><?php echo intval($row->attempt_count); ?></strong>
                                    <?php if (intval($row->attempt_count) >= ($rate_limit_attempts - 1)) : ?>
                                        <span style="color: #d63638;"> ⚠️</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($row->last_attempt); ?></td>
                                <td><?php echo esc_html($status_text); ?></td>
                                <td>
                                    <form method="post" style="margin:0;">
                                        <?php wp_nonce_field('caldps_clear_ip'); ?>
                                        <input type="hidden" name="caldps_ip_to_clear" value="<?php echo esc_attr($row->ip_address); ?>" />
                                        <button class="button button-small" name="caldps_clear_ip" value="1">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php
}

// === Các hàm bảo mật ===

// Cập nhật .htaccess (giữ lại để clean up cũ)
function caldps_update_htaccess() {
    $htaccess_file = ABSPATH . '.htaccess';

    // Only attempt to modify if the file already exists
    if (!file_exists($htaccess_file)) {
        return;
    }

    // Initialize WP_Filesystem
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    if (!function_exists('WP_Filesystem')) {
        return;
    }

    WP_Filesystem();
    global $wp_filesystem;

    if (!$wp_filesystem || !$wp_filesystem->is_writable($htaccess_file)) {
        add_settings_error(
            'dps_login_security_settings',
            'htaccess_not_writable',
            __('Cannot write to .htaccess file. Please check file permissions.', 'dps-login-security'),
            'error'
        );
        return;
    }

    $htaccess_content = $wp_filesystem->get_contents($htaccess_file);

    // Remove old plugin rules (including XML-RPC rules)
    $htaccess_content = preg_replace('/# BEGIN DPS Security.*?# END DPS Security\s*/s', '', $htaccess_content);
    $htaccess_content = preg_replace('/# BEGIN DPS XML-RPC Block.*?# END DPS XML-RPC Block\s*/s', '', $htaccess_content);

    // Write back the cleaned content
    if (!$wp_filesystem->put_contents($htaccess_file, $htaccess_content)) {
        add_settings_error(
            'dps_login_security_settings',
            'htaccess_write_failed',
            __('Failed to write to .htaccess file.', 'dps-login-security'),
            'error'
        );
    }
}

// Tắt file editing
if (get_option('caldps_disable_file_edit', 0)) {
    if (!defined('DISALLOW_FILE_EDIT')) {
        define('DISALLOW_FILE_EDIT', true);
    }
    if (!defined('DISALLOW_FILE_MODS')) {
        define('DISALLOW_FILE_MODS', true);
    }
}





// Tắt error reporting
if (get_option('caldps_disable_error_reporting', 0)) {
    if (!defined('WP_DEBUG')) {
        define('WP_DEBUG', false);
    }
    if (!defined('WP_DEBUG_LOG')) {
        define('WP_DEBUG_LOG', false);
    }
    if (!defined('WP_DEBUG_DISPLAY')) {
        define('WP_DEBUG_DISPLAY', false);
    }
    // ini_set('display_errors', 0) removed as discouraged for plugins.
}

// Tắt pingbacks và trackbacks
if (get_option('caldps_disable_pingbacks', 0)) {
    add_filter('xmlrpc_methods', function($methods) {
        unset($methods['pingback.ping']);
        unset($methods['pingback.extensions.getPingbacks']);
        return $methods;
    });
    
    add_action('pre_ping', function(&$links) {
        $links = array();
    });
    
    add_filter('wp_headers', function($headers) {
        if (isset($headers['X-Pingback'])) {
            unset($headers['X-Pingback']);
        }
        return $headers;
    });
}

// Ẩn WordPress version
if (get_option('caldps_hide_wp_version', 0)) {
    remove_action('wp_head', 'wp_generator');
    add_filter('the_generator', '__return_empty_string');
    
    // Ẩn version khỏi RSS
    add_filter('the_generator', function() {
        return '';
    });
}

// Ẩn plugin/theme version
if (get_option('caldps_hide_plugin_version', 0)) {
    add_filter('style_loader_src', function($src) {
        if (strpos($src, 'ver=')) {
            $src = remove_query_arg('ver', $src);
        }
        return $src;
    }, 9999);
    
    add_filter('script_loader_src', function($src) {
        if (strpos($src, 'ver=')) {
            $src = remove_query_arg('ver', $src);
        }
        return $src;
    }, 9999);
}

// === Rate Limiting Functions ===

// Tạo bảng log rate limiting khi plugin activated
register_activation_hook(__FILE__, 'caldps_create_rate_limit_table');

// Activation hook to flush rewrite rules
register_activation_hook(__FILE__, 'dps_login_security_activate');

function dps_login_security_activate() {
    dps_login_security_seed_defaults();
    update_option('dps_login_security_version', DPS_LOGIN_SECURITY_VERSION);

    // Create both database tables
    caldps_create_rate_limit_table();
    dps_create_security_log_table();

    // Flush rewrite rules
    flush_rewrite_rules();
}

// Clean up rate limit table khi plugin deactivated
register_deactivation_hook(__FILE__, 'caldps_cleanup_rate_limit_table');

// Uninstall hook to clean up all data
register_uninstall_hook(__FILE__, 'dps_login_security_uninstall');

function dps_login_security_uninstall() {
    dps_login_security_delete_plugin_settings();

    // Clean up .htaccess
    caldps_update_htaccess();

    // Drop rate limit table
    caldps_cleanup_rate_limit_table();
}

function dps_login_security_delete_plugin_settings() {
    $options = array(
        'caldps_slug',
        'caldps_greeting',
        'caldps_logo',
        'caldps_left_custom_html',
        'caldps_left_custom_css',
        'caldps_disable_file_edit',
        'caldps_disable_error_reporting',
        'caldps_disable_pingbacks',
        'caldps_hide_wp_version',
        'caldps_hide_plugin_version',
        'caldps_enable_rate_limit',
        'caldps_rate_limit_attempts',
        'caldps_rate_limit_time',
        'caldps_rate_limit_block_time',
        'caldps_needs_rewrite_flush',
        'dps_login_security_version'
    );

    foreach ($options as $option) {
        delete_option($option);
    }
}

// Ensure rate limit table exists even if activation hook didn't run (e.g., manual file copy/update)
add_action('init', function() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'dps_rate_limit';
    $existing = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
    if ($existing !== $table_name) {
        caldps_create_rate_limit_table();
    }
    
    // Also ensure security log table exists
    $log_table = $wpdb->prefix . 'dps_security_log';
    $log_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $log_table));
    if ($log_exists !== $log_table) {
        dps_create_security_log_table();
    }
}, 0);

// Mark the custom login slug as uncacheable to avoid cached pages bypassing rate limits
add_action('init', function() {
    $slug = get_option('caldps_slug', 'admindps');
    $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';

    if ($slug && $request_uri && strpos($request_uri, '/' . trim($slug, '/') . '/') !== false) {
        if (!defined('DONOTCACHEPAGE')) { define('DONOTCACHEPAGE', true); }
        if (!defined('DONOTCACHEDB')) { define('DONOTCACHEDB', true); }
        if (!defined('DONOTMINIFY')) { define('DONOTMINIFY', true); }
        if (!defined('DONOTCDN')) { define('DONOTCDN', true); }
    }
}, 1);

// === Emergency Access Method ===
// Allow access via special query parameter for emergency situations
add_action('init', function() {
    if (isset($_GET['dps_emergency_login']) && $_GET['dps_emergency_login'] === 'true') {
        // Only allow if user is already logged in (to prevent abuse)
        if (is_user_logged_in() && current_user_can('manage_options')) {
            // Show current login URL
            $slug = get_option('caldps_slug', 'admindps');
            $login_url = home_url("/$slug/");

            wp_die(
                '<h2>Emergency Access</h2>' .
                '<p><strong>Current Login URL:</strong> <a href="' . esc_url($login_url) . '">' . esc_html($login_url) . '</a></p>' .
                '<p><em>This emergency page is only visible to administrators who are already logged in.</em></p>' .
                '<p><a href="' . esc_url(admin_url()) . '">← Back to Admin</a></p>',
                'DPS Login Security - Emergency Access',
                array('response' => 200)
            );
        }
    }
});

// Add admin notice showing current login URL
add_action('admin_notices', function() {
    if (current_user_can('manage_options')) {
        $slug = get_option('caldps_slug', 'admindps');
        $login_url = home_url("/$slug/");
        $emergency_url = add_query_arg('dps_emergency_login', 'true', home_url());

        echo '<div class="notice notice-info is-dismissible">' .
             '<p><strong>DPS Login Security:</strong> ' .
             'Login URL: <code><a href="' . esc_url($login_url) . '">' . esc_html($login_url) . '</a></code> | ' .
             '<a href="' . esc_url($emergency_url) . '">Emergency Access</a></p>' .
             '</div>';
    }
});

function caldps_cleanup_rate_limit_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'dps_rate_limit';

    // Xóa toàn bộ table khi deactivate
    $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
}

function caldps_create_rate_limit_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'dps_rate_limit';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        ip_address varchar(45) NOT NULL,
        attempt_count int(11) NOT NULL DEFAULT 1,
        last_attempt datetime DEFAULT CURRENT_TIMESTAMP,
        is_blocked tinyint(1) NOT NULL DEFAULT 0,
        blocked_until datetime NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY ip_address (ip_address),
        KEY blocked_until (blocked_until)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Create security log table for advanced protection features
function dps_create_security_log_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'dps_security_log';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        event_type varchar(50) NOT NULL,
        ip_address varchar(45) NOT NULL,
        uri varchar(255) NULL,
        user_agent text NULL,
        details text NULL,
        severity varchar(20) DEFAULT 'medium',
        timestamp datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY event_type (event_type),
        KEY ip_address (ip_address),
        KEY timestamp (timestamp)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Hàm lấy IP address của client
function caldps_get_client_ip() {
    $ip_keys = array('HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR');

    foreach ($ip_keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ips = explode(',', $_SERVER[$key]);
            $ip = trim($ips[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }

    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

// Helper to cache block state so a block persists even if the DB update fails
function caldps_rate_limit_get_block_key($ip) {
    return 'caldps_rl_block_' . md5($ip);
}

function caldps_rate_limit_cache_block($ip, $blocked_until_ts) {
    if (empty($blocked_until_ts)) {
        return;
    }
    $ttl = max(1, (int)$blocked_until_ts - current_time('timestamp'));
    set_transient(caldps_rate_limit_get_block_key($ip), (int)$blocked_until_ts, $ttl);
}

function caldps_rate_limit_clear_block_cache($ip) {
    delete_transient(caldps_rate_limit_get_block_key($ip));
}

// Hàm kiểm tra rate limiting
function caldps_check_rate_limit($ip = null) {
    // Backward-compatible wrapper: check-only (no increment). Returns true if allowed, false if blocked.
    return !caldps_rate_limit_is_blocked($ip);
}

// Check if current IP is blocked without incrementing counters
function caldps_rate_limit_is_blocked($ip = null) {
    if (!get_option('caldps_enable_rate_limit', 0)) {
        return false;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'dps_rate_limit';

    $ip = $ip ?: caldps_get_client_ip();
    $current_time = current_time('mysql');
    $current_ts = current_time('timestamp');
    $block_key = caldps_rate_limit_get_block_key($ip);

    // Quick cached block check (covers cases where DB update failed)
    $cached_until = get_transient($block_key);
    if ($cached_until && (int)$cached_until > $current_ts) {
        return true;
    } elseif ($cached_until && (int)$cached_until <= $current_ts) {
        // Clean up stale cache
        caldps_rate_limit_clear_block_cache($ip);
    }

    // Remove expired blocks
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$table_name} WHERE is_blocked = 1 AND blocked_until <= %s",
        $current_time
    ));

    // Check active block
    $blocked_until = $wpdb->get_var($wpdb->prepare(
        "SELECT blocked_until FROM {$table_name} WHERE ip_address = %s AND is_blocked = 1 AND blocked_until > %s LIMIT 1",
        $ip, $current_time
    ));

    if (!empty($blocked_until)) {
        $blocked_ts = strtotime($blocked_until);
        if ($blocked_ts && $blocked_ts > $current_ts) {
            // Cache the block window to avoid any DB reliability issues
            caldps_rate_limit_cache_block($ip, $blocked_ts);
            return true;
        }
    }

    return false;
}

function caldps_get_rate_limit_block_notice($ip = null) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'dps_rate_limit';

    $ip = $ip ?: caldps_get_client_ip();
    $current_time = current_time('mysql');
    $message = 'Quá số lần thử tối đa. Vui lòng thử lại sau.';
    $retry_after = 0;

    // Prefer cached block window first
    $cached_until = get_transient(caldps_rate_limit_get_block_key($ip));
    $now_ts = current_time('timestamp');
    if ($cached_until && (int)$cached_until > $now_ts) {
        $retry_after = max(0, (int)$cached_until - $now_ts);
        $message = 'Quá số lần thử tối đa. Vui lòng thử lại sau lúc ' . date_i18n('H:i:s', (int)$cached_until) . '.';
        return array($message, $retry_after);
    }

    $blocked_info = $wpdb->get_row($wpdb->prepare(
        "SELECT blocked_until FROM {$table_name} WHERE ip_address = %s AND is_blocked = 1",
        $ip
    ));

    if ($blocked_info && !empty($blocked_info->blocked_until)) {
        $unblock_ts = strtotime($blocked_info->blocked_until);
        if ($unblock_ts > $now_ts) {
            $retry_after = max(0, $unblock_ts - $now_ts);
            $message = 'Quá số lần thử tối đa. Vui lòng thử lại sau lúc ' . date_i18n('H:i:s', $unblock_ts) . '.';
            // Cache the block window so subsequent checks remain consistent
            caldps_rate_limit_cache_block($ip, $unblock_ts);
        } else {
            caldps_rate_limit_clear_block_cache($ip);
        }
    }

    return array($message, $retry_after);
}

function caldps_die_rate_limit_block($ip = null) {
    list($message, $retry_after) = caldps_get_rate_limit_block_notice($ip);

    status_header(403);
    header('HTTP/1.0 403 Forbidden');
    if ($retry_after > 0) {
        @header('Retry-After: ' . (int) $retry_after);
    }

    die('Forbidden');
}

// Record a failed attempt; increments count and blocks if threshold exceeded
function caldps_rate_limit_record_failure($ip = null) {
    if (!get_option('caldps_enable_rate_limit', 0)) {
        return array('blocked' => false, 'count' => 0);
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'dps_rate_limit';

    $ip = $ip ?: caldps_get_client_ip();
    $max_attempts = get_option('caldps_rate_limit_attempts', 5);
    $time_window = get_option('caldps_rate_limit_time', 15); // minutes
    $block_time = get_option('caldps_rate_limit_block_time', 60); // minutes

    $current_time = current_time('mysql');
    $time_threshold = gmdate('Y-m-d H:i:s', strtotime("-{$time_window} minutes", strtotime($current_time)));

    // Fetch row for IP
    $attempts = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE ip_address = %s", $ip));

    if (!$attempts) {
        // First failure
        $wpdb->insert(
            $table_name,
            array(
                'ip_address' => $ip,
                'attempt_count' => 1,
                'last_attempt' => $current_time,
                'is_blocked' => 0,
                'blocked_until' => null,
            ),
            array('%s','%d','%s','%d','%s')
        );
        return array('blocked' => false, 'count' => 1);
    }

    // If currently blocked and still active, return blocked without changing counts
    if ((int)$attempts->is_blocked === 1 && strtotime($attempts->blocked_until) > strtotime($current_time)) {
        caldps_rate_limit_cache_block($ip, strtotime($attempts->blocked_until));
        return array('blocked' => true, 'count' => (int)$attempts->attempt_count);
    }

    // Reset if outside time window
    $count = (strtotime($attempts->last_attempt) < strtotime($time_threshold)) ? 0 : (int)$attempts->attempt_count;

    $new_count = $count + 1; // this failure

    if ($new_count > $max_attempts) {
        $blocked_until = gmdate('Y-m-d H:i:s', strtotime("+{$block_time} minutes", strtotime($current_time)));
        $wpdb->update(
            $table_name,
            array(
                'attempt_count' => $new_count,
                'last_attempt' => $current_time,
                'is_blocked' => 1,
                'blocked_until' => $blocked_until,
            ),
            array('ip_address' => $ip),
            array('%d','%s','%d','%s'),
            array('%s')
        );
        caldps_rate_limit_cache_block($ip, strtotime($blocked_until));
        return array('blocked' => true, 'count' => $new_count);
    }

    // Update count within window
    $wpdb->update(
        $table_name,
        array(
            'attempt_count' => $new_count,
            'last_attempt' => $current_time,
            'is_blocked' => 0,
            'blocked_until' => null,
        ),
        array('ip_address' => $ip),
        array('%d','%s','%d','%s'),
        array('%s')
    );

    return array('blocked' => false, 'count' => $new_count);
}

// Hàm reset rate limit khi đăng nhập thành công
function caldps_reset_rate_limit($ip = null) {
    if (!get_option('caldps_enable_rate_limit', 0)) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'dps_rate_limit';

    $ip = $ip ?: caldps_get_client_ip();

    $wpdb->delete(
        $table_name,
        array('ip_address' => $ip)
    );
    caldps_rate_limit_clear_block_cache($ip);
}

// === Rewrite login slug ===
add_action('init', function() {
    $slug = get_option('caldps_slug', 'admindps');
    add_rewrite_rule("^$slug/?$", 'index.php?caldps_custom_login=1', 'top');

    // Ensure rewrite rule exists; if missing and pretty permalinks enabled, schedule a hard flush
    $permalink_structure = get_option('permalink_structure');
    if (!empty($permalink_structure)) {
        $rules = get_option('rewrite_rules');
        $pattern = '^' . preg_quote($slug, '/') . '/?$';
        $has_rule = false;
        if (is_array($rules)) {
            if (isset($rules[$pattern]) && strpos($rules[$pattern], 'caldps_custom_login=1') !== false) {
                $has_rule = true;
            } else {
                // Fallback: search values for our query var mapping
                foreach ($rules as $k => $v) {
                    if (strpos($v, 'caldps_custom_login=1') !== false) { $has_rule = true; break; }
                }
            }
        }
        if (!$has_rule) {
            update_option('caldps_needs_rewrite_flush', true);
        }
    }

    // Check if we need to flush rewrite rules
    if (get_option('caldps_needs_rewrite_flush', false)) {
        // Use hard flush so rules persist without needing to save Permalinks
        flush_rewrite_rules();
        delete_option('caldps_needs_rewrite_flush');
    }
}, 10);

add_filter('query_vars', function($vars){
    $vars[] = 'caldps_custom_login';
    return $vars;
});

// === 1. Chặn hoặc Chuyển hướng truy cập wp-login.php và /wp-admin ===
// Sử dụng setup_theme để chặn sớm hơn init, giống các plugin bảo mật chuyên nghiệp
add_action('setup_theme', function(){
    global $pagenow;
    
    // 1. Bỏ qua AJAX và Cron
    if ((defined('DOING_AJAX') && DOING_AJAX) || (defined('DOING_CRON') && DOING_CRON)) return;
    
    // 2. QUAN TRỌNG: Bỏ qua custom login slug NGAY TỪ ĐẦU để tránh redirect loop
    $slug = get_option('caldps_slug', 'admindps');
    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    
    // Kiểm tra chính xác custom slug (phải có dấu / trước và sau hoặc cuối URI)
    if (preg_match('#/' . preg_quote($slug, '#') . '(/|$)#', $uri)) {
        return; // Cho phép truy cập vào trang login custom
    }
    
    // 3. Nhận diện các đường dẫn mặc định
    $script_name = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';

    // Kiểm tra trang login (wp-login.php)
    $is_login_page = (
        $pagenow === 'wp-login.php' || 
        strpos($uri, 'wp-login.php') !== false || 
        strpos($script_name, 'wp-login.php') !== false
    );
    
    // Kiểm tra trang admin (/wp-admin) - CHỈ kiểm tra /wp-admin, KHÔNG kiểm tra /admin
    $is_admin_request = (
        is_admin() || 
        strpos($uri, '/wp-admin') !== false
    );
    
    // Bỏ qua nếu đã login
    if (is_user_logged_in()) return;

    // Filter out AJAX và Post requests cho admin (cho phép chạy bình thường)
    if ($is_admin_request && (strpos($uri, 'admin-ajax.php') !== false || strpos($uri, 'admin-post.php') !== false)) return;

    if (!$is_login_page && !$is_admin_request) return;

    // 4. Ngoại lệ cho các hành động mặc định của WordPress (Mất mật khẩu, Logout)
    if ($is_login_page) {
        $action = isset($_REQUEST['action']) ? sanitize_key($_REQUEST['action']) : '';
        $allowed_actions = array('lostpassword', 'retrievepassword', 'rp', 'resetpass', 'postpass', 'logout');
        if (in_array($action, $allowed_actions, true)) return;
    }
    
    // 5. Xử lý Chặn (403) hoặc Chuyển hướng (Redirect)
    $block_standard = intval(get_option('dps_block_standard_login', 0));

    if ($block_standard) {
        DPS_Security_Logger::log('standard_login_blocked', "Blocked access attempt: " . $uri, 'high');
        status_header(403);
        nocache_headers();
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        wp_die('Access Denied. Standard login is disabled for security.', 'Forbidden', array('response' => 403));
    } else {
        wp_safe_redirect(home_url("/$slug/"));
        exit;
    }
}, 1);

// Gỡ bỏ bộ điều hướng mặc định của WordPress để tránh nó redirect trước plugin
remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );

// Chặn các lệnh Redirect ngầm về wp-login.php từ các plugin khác
add_filter('wp_redirect', function($location){
    if (is_user_logged_in()) return $location;
    if (!intval(get_option('dps_block_standard_login', 0))) return $location;

    if (strpos($location, 'wp-login.php') !== false || strpos($location, 'wp-admin') !== false) {
        // Nếu đang cố redirect về login chuẩn, chặn đứng trả về 403
        status_header(403);
        wp_die('Access Denied. Internal redirect to standard login blocked.', 'Forbidden', array('response' => 403));
    }
    return $location;
}, 10, 1);

// === 2. Render trang login custom ===
add_action('template_redirect', function() {
    if (intval(get_query_var('caldps_custom_login')) !== 1) return;
    if (is_user_logged_in()) {
        wp_safe_redirect(admin_url());
        exit;
    }
    $greeting = get_option('caldps_greeting', 'Chào mừng bạn quay lại!');
    $logo = get_option('caldps_logo', '');
    // Lấy raw không escape khi render ra login - sử dụng mặc định nếu trống
    $left_custom_html = get_option('caldps_left_custom_html', '');
    if (empty($left_custom_html)) {
        $left_custom_html = caldps_get_default_html();
    }
    $left_custom_css = get_option('caldps_left_custom_css', '');
    if (empty($left_custom_css)) {
        $left_custom_css = caldps_get_default_css();
    }
    $redirect_to = isset($_GET['redirect_to']) ? esc_url_raw($_GET['redirect_to']) : admin_url();
    $error = '';
    $is_blocked = false;
    $retry_after = 0;

    // Check rate limiting before processing login (check-only)
    if (get_option('caldps_enable_rate_limit', 0) && caldps_rate_limit_is_blocked()) {
        list($error, $retry_after) = caldps_get_rate_limit_block_notice();
        $is_blocked = true;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log'], $_POST['pwd'], $_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'caldps_login')) {
        // Check rate limit before processing login (check-only)
        if (get_option('caldps_enable_rate_limit', 0) && caldps_rate_limit_is_blocked()) {
            list($error, $retry_after) = caldps_get_rate_limit_block_notice();
            $is_blocked = true;
        } else {
            $user = wp_signon([
                'user_login' => $_POST['log'],
                'user_password' => $_POST['pwd'],
                'remember' => !empty($_POST['rememberme']),
            ], false);

            if (is_wp_error($user)) {
                // Login failed - increment rate limit counter
                $error = $user->get_error_message();
                if (get_option('caldps_enable_rate_limit', 0)) {
                    $rate_result = caldps_rate_limit_record_failure();
                    if (!empty($rate_result['blocked'])) {
                        list($error, $retry_after) = caldps_get_rate_limit_block_notice();
                        $is_blocked = true;
                    }
                }
            } else {
                // Login successful - reset rate limit
                if (get_option('caldps_enable_rate_limit', 0)) {
                    caldps_reset_rate_limit();
                }
                wp_set_current_user($user->ID);
                wp_set_auth_cookie($user->ID, !empty($_POST['rememberme']));
                do_action('wp_login', $user->user_login, $user);
                wp_safe_redirect( wp_validate_redirect( $redirect_to, admin_url() ) );
                exit;
            }
        }
    }
    if ($is_blocked) {
        nocache_headers();
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('X-DPS-RateLimit', 'blocked');
        status_header(403);
        header('HTTP/1.0 403 Forbidden');
        if ($retry_after > 0) {
            @header('Retry-After: ' . (int) $retry_after);
        }
        die('Forbidden');
    } else {
        nocache_headers();
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('X-DPS-RateLimit', 'ok');
        status_header(200);
    }
    ?>
    <!DOCTYPE html>
    <html><head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Đăng nhập | DPS</title>
        <style>
        body { margin:0; background:#f3f7fa; font-family: Arial, system-ui, sans-serif;}
        .caldps-wrap { min-height:100vh; display:flex; align-items:center; justify-content:center; background:#f3f7fa;}
        .caldps-card { box-shadow:0 8px 40px 0 rgba(31,38,135,.11); border-radius:36px; background:#fff; display:flex; width:1100px; min-height:620px; overflow:hidden;}
        .caldps-left-custom { width:50vw; min-width:410px; min-height:620px; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:0; background:none;}
        .caldps-right { width:50vw; min-width:340px; padding:62px 48px; display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:620px; background:#fff;}
        .caldps-logo { width:256px; margin-bottom:23px; }
        .caldps-greeting { font-size:1.16rem; margin-bottom:24px; color:#232; font-weight:800; text-align:center; }
        .caldps-form { width:100%; max-width:340px; margin-top:8px; }
        .caldps-form input { width:100%; margin-bottom:18px; padding:13px 14px; border-radius:9px; border:1.5px solid #d5e9d5; font-size:1rem; font-family:inherit; background:#f8fdf8; transition:.16s;}
        .caldps-form input:focus { border-color:#65af2f; outline:none; background:#fff;}
        .caldps-form button { width:100%; background:#65af2f; color:#fff; border:none; padding:13px 0; border-radius:9px; font-size:1.09rem; font-weight:800; cursor:pointer; font-family:inherit; box-shadow:0 2px 8px #51ce6e33;}
        .caldps-form button:hover { background:#3fd16c;}
        .caldps-form .caldps-err { color:#d51c27; font-size:.99em; margin-bottom:17px; text-align:center; }
        .caldps-link { display:block; margin-top:12px; font-size:1em; text-align:right; color:#51ce6e; text-decoration:none; font-weight:600;}
        .caldps-link:hover { text-decoration:underline; color:#17813b;}
        @media(max-width:1100px){
            .caldps-card{flex-direction:column; width:99vw; min-width:340px;}
            .caldps-left-custom,.caldps-right{width:100vw;min-width:unset;min-height:380px;}
            .caldps-left-custom{padding:32px 4vw;}
        }
        @media(max-width:700px){
            .caldps-card{box-shadow:none;border-radius:0;width:100vw;min-height:0;}
            .caldps-left-custom,.caldps-right{padding:22px 4vw; min-height:0;}
        }
        <?php if($left_custom_css): ?>
        /* CUSTOM CSS FROM SETTINGS */
        <?php echo wp_strip_all_tags($left_custom_css); ?>
        <?php endif; ?>
        
@media (max-width: 900px) {
  .caldps-card {
    flex-direction: column-reverse !important;
  }
  .caldps-left-custom, .dps-login-left {
    width: 100% !important;
    max-width: 100vw !important;
    margin: 0 auto !important;
  }
  .caldps-right {
    width: 100% !important;
    max-width: 100vw !important;
    min-width: unset !important;
    margin: 0 auto !important;
    box-shadow: none !important;
  }
}

</style>
    </head>
    <body>
        <div class="caldps-wrap">
            <div class="caldps-card">
                <div class="caldps-left-custom">
                    <?php echo wp_kses_post($left_custom_html); ?>
                </div>
                <div class="caldps-right">
                    <?php if($logo): ?><img src="<?php echo esc_url($logo); ?>" class="caldps-logo"><?php endif; ?>
                    <div class="caldps-greeting"><?php echo esc_html($greeting); ?></div>
                    <form class="caldps-form" method="post" autocomplete="on">
                        <?php wp_nonce_field('caldps_login'); ?>
                        <?php if($error): ?><div class="caldps-err"><?php echo wp_kses_post($error); ?></div><?php endif; ?>
                        <input type="text" name="log" placeholder="Tên đăng nhập" required autofocus autocomplete="username" <?php echo $is_blocked ? 'disabled aria-disabled="true"' : ''; ?>>
                        
<input type="password" name="pwd" placeholder="Mật khẩu" required autocomplete="current-password" <?php echo $is_blocked ? 'disabled aria-disabled="true"' : ''; ?>>
<button type="button" id="dps-toggle-password" style="margin-left:8px;" <?php echo $is_blocked ? 'disabled aria-disabled="true"' : ''; ?>>Che mật khẩu</button>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var pwd = document.querySelector('input[type="password"]');
    var btn = document.getElementById('dps-toggle-password');
    if (pwd && btn) {
        btn.addEventListener('click', function() {
            if (pwd.type === "password") {
                pwd.type = "text";
                btn.textContent = "Ẩn mật khẩu";
            } else {
                pwd.type = "password";
                btn.textContent = "Hiện mật khẩu";
            }
        });
        // Đặt text mặc định là "Hiện mật khẩu"
        btn.textContent = "Hiện mật khẩu";
    }
});
</script>


                        <label style="font-size:.99em"><input type="checkbox" name="rememberme" value="1" checked <?php echo $is_blocked ? 'disabled aria-disabled="true"' : ''; ?>> Ghi nhớ đăng nhập</label>
                        <button type="submit" <?php echo $is_blocked ? 'disabled aria-disabled="true"' : ''; ?>>Đăng nhập</button>
                        <a class="caldps-link" href="<?php echo esc_url(wp_lostpassword_url()); ?>">Quên mật khẩu?</a>
                    </form>
                </div>
            </div>
        </div>
    </body></html>
    <?php
    exit;
});

// ==========================================
// === SECURITY MODULES (v7.0) ===
// ==========================================

/**
 * Security Logger - Centralized logging for all security events
 */
if (!class_exists('DPS_Security_Logger')) {
class DPS_Security_Logger {
    /**
     * Log a security event
     */
    public static function log($event_type, $details, $severity = 'medium') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dps_security_log';
        
        $wpdb->insert($table_name, array(
            'event_type' => sanitize_key($event_type),
            'ip_address' => caldps_get_client_ip(),
            'uri' => sanitize_text_field($_SERVER['REQUEST_URI'] ?? ''),
            'user_agent' => sanitize_text_field(substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)),
            'details' => sanitize_text_field($details),
            'severity' => sanitize_key($severity),
            'timestamp' => current_time('mysql')
        ));
    }
    
    /**
     * Get recent security events
     */
    public static function get_recent_events($limit = 100, $event_type = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dps_security_log';
        
        if ($event_type) {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE event_type = %s ORDER BY timestamp DESC LIMIT %d",
                $event_type,
                $limit
            ));
        }
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} ORDER BY timestamp DESC LIMIT %d",
            $limit
        ));
    }
    
    /**
     * Cleanup old logs (30 days)
     */
    public static function cleanup_old_logs() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dps_security_log';
        $retention_days = DPS_SECURITY_LOG_RETENTION_DAYS;
        
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$table_name} WHERE timestamp < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $retention_days
        ));
    }
}
}

// Schedule daily log cleanup
if (!wp_next_scheduled('dps_security_log_cleanup')) {
    wp_schedule_event(time(), 'daily', 'dps_security_log_cleanup');
}
add_action('dps_security_log_cleanup', array('DPS_Security_Logger', 'cleanup_old_logs'));

/**
 * XMLRPC Blocker - Complete XMLRPC blocking (HIGH PRIORITY)
 */
if (!class_exists('DPS_XMLRPC_Blocker')) {
class DPS_XMLRPC_Blocker {
    public static function init() {
        if (get_option('dps_block_xmlrpc', 1)) { // Enabled by default
            // Disable XMLRPC functionality
            add_filter('xmlrpc_enabled', '__return_false', 9999);
            
            // Block direct access to xmlrpc.php
            add_action('init', array(__CLASS__, 'block_xmlrpc_access'), 1);
        }
    }
    
    public static function block_xmlrpc_access() {
        if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
            DPS_Security_Logger::log('xmlrpc_blocked', 'XMLRPC request blocked via XMLRPC_REQUEST constant', 'high');
            http_response_code(403);
            header('Content-Type: text/plain; charset=utf-8');
            nocache_headers();
            exit; // No message body - clean 403
        }
        
        // Block direct xmlrpc.php file access
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        if (stripos($request_uri, 'xmlrpc.php') !== false) {
            DPS_Security_Logger::log('xmlrpc_blocked', 'Direct XMLRPC file access attempt', 'high');
            http_response_code(403);
            header('Content-Type: text/plain; charset=utf-8');
            nocache_headers();
            exit; // No message body - clean 403
        }
    }
}
}

/**
 * Request Validator - Query strings, HTTP methods, sensitive files
 */
if (!class_exists('DPS_Request_Validator')) {
class DPS_Request_Validator {
    public static function init() {
        add_action('init', array(__CLASS__, 'validate_request'), 1);
    }
    
    public static function validate_request() {
        // HTTP Method validation
        if (get_option('dps_block_bad_methods', 0)) {
            self::validate_http_method();
        }
        
        // Query string validation
        if (get_option('dps_validate_query_strings', 0)) {
            self::validate_query_string();
        }
        
        // Sensitive file protection
        if (get_option('dps_block_sensitive_files', 0)) {
            self::block_sensitive_files();
        }
    }
    
    private static function validate_http_method() {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $blocked_methods = array('TRACE', 'TRACK', 'CONNECT', 'MOVE', 'DEBUG', 'DELETE', 'PATCH');
        
        if (in_array($method, $blocked_methods)) {
            DPS_Security_Logger::log('bad_http_method', "Method: $method", 'medium');
            status_header(405);
            nocache_headers();
            die('Method Not Allowed');
        }
    }
    
    private static function validate_query_string() {
        $query = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($query)) {
            return;
        }
        
        $patterns = array(
            '/\.\.\//i' => 'Directory Traversal',
            '/union.*select/i' => 'SQL Injection',
            '/etc\/passwd/i' => 'LFI Attempt',
            '/eval\(/i' => 'Code Injection',
            '/<script/i' => 'XSS Attempt',
            '/base64_decode/i' => 'Obfuscation',
            '/concat\(/i' => 'SQL Injection',
            '/exec\(/i' => 'Command Injection',
        );
        
        foreach ($patterns as $pattern => $reason) {
            if (preg_match($pattern, $query)) {
                DPS_Security_Logger::log('malicious_query', $reason . " - Query: " . substr($query, 0, 100), 'high');
                status_header(403);
                nocache_headers();
                die('Malicious Request Detected');
            }
        }
    }
    
    private static function block_sensitive_files() {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        
        $patterns = array(
            '/wp-config\.php/i' => 'wp-config.php',
            '/\.env/i' => '.env file',
            '/\.git\//i' => '.git directory',
            '/\.sql$/i' => 'SQL file',
            '/\.tar\.gz$/i' => 'Archive file',
            '/\.zip$/i' => 'ZIP file',
            '/\.bak$/i' => 'Backup file',
            '/\.backup$/i' => 'Backup file',
            '/readme\.html/i' => 'readme.html',
            '/license\.txt/i' => 'license.txt',
            '/\.htaccess/i' => '.htaccess',
            '/\.user\.ini/i' => '.user.ini',
        );
        
        foreach ($patterns as $pattern => $file_type) {
            if (preg_match($pattern, $uri)) {
                DPS_Security_Logger::log('sensitive_file_blocked', "File type: $file_type - URI: $uri", 'high');
                status_header(403);
                nocache_headers();
                die('Access Denied');
            }
        }
    }
}
}

/**
 * User Agent Filter - Bad bots and Googlebot verification
 */
if (!class_exists('DPS_User_Agent_Filter')) {
class DPS_User_Agent_Filter {
    private static $bad_agents = array(
        'sqlmap', 'nikto', 'masscan', 'nmap', 'python-urllib', 
        'go-http-client', 'Bytespider', 'MJ12bot', 'AhrefsBot', 
        'SemrushBot', 'DotBot', 'PetalBot', 'MegaIndex', 'SeznamBot',
        'AspiegelBot', 'BLEXBot', 'Riddler', 'ZoomBot', 'linkdexbot'
    );
    
    public static function init() {
        if (get_option('dps_block_bad_agents', 0)) {
            add_action('init', array(__CLASS__, 'check_user_agent'), 1);
        }
    }
    
    public static function check_user_agent() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (empty($user_agent)) {
            // Block empty user agents
            DPS_Security_Logger::log('empty_user_agent', 'Empty user agent blocked', 'medium');
            status_header(403);
            nocache_headers();
            die('Access Denied');
        }
        
        // Check against bad agent list
        foreach (self::$bad_agents as $agent) {
            if (stripos($user_agent, $agent) !== false) {
                DPS_Security_Logger::log('bad_agent_blocked', "Agent: $agent - Full UA: " . substr($user_agent, 0, 100), 'medium');
                status_header(403);
                nocache_headers();
                die('Access Denied');
            }
        }
        
        // Googlebot verification (optional)
        if (get_option('dps_verify_googlebot', 0)) {
            self::verify_googlebot($user_agent);
        }
    }
    
    private static function verify_googlebot($user_agent) {
        // Check if claiming to be Googlebot
        if (stripos($user_agent, 'Googlebot') !== false || 
            stripos($user_agent, 'AdsBot-Google') !== false ||
            stripos($user_agent, 'Mediapartners-Google') !== false) {
            
            $ip = caldps_get_client_ip();
            $hostname = gethostbyaddr($ip);
            
            // Verify reverse DNS matches Google domains
            if (!preg_match('/\.(googlebot|google)\.com$/i', $hostname)) {
                DPS_Security_Logger::log('fake_googlebot', "IP: $ip - Hostname: $hostname", 'high');
                status_header(403);
                nocache_headers();
                die('Fake Googlebot Detected');
            }
        }
    }
}
}

/**
 * Login Scanner - Detect automated login scanning patterns (HIGH PRIORITY)
 */
if (!class_exists('DPS_Login_Scanner')) {
class DPS_Login_Scanner {
    public static function init() {
        if (get_option('dps_detect_login_scan', 1)) { // Enabled by default
            // Block username enumeration via author pages
            add_action('init', array(__CLASS__, 'block_user_enumeration'), 1);
            
            // Log failed login attempts with common usernames
            add_action('wp_login_failed', array(__CLASS__, 'log_failed_username'));
        }
    }
    
    public static function block_user_enumeration() {
        if (get_option('dps_block_user_enum', 1)) {
            $request_uri = $_SERVER['REQUEST_URI'] ?? '';
            
            // Block author enumeration via /?author=N
            if (preg_match('/[?&]author=(\d+)/i', $request_uri)) {
                DPS_Security_Logger::log('user_enum_blocked', 'Author page enumeration attempt', 'medium');
                status_header(403);
                nocache_headers();
                die('Forbidden');
            }
        }
    }
    
    public static function log_failed_username($username) {
        // Track attempts with common admin usernames
        $common_usernames = array('admin', 'administrator', 'root', 'user', 'test', 'demo', 'guest', 'wp-admin', 'support');
        
        if (in_array(strtolower($username), $common_usernames)) {
            DPS_Security_Logger::log('common_username_scan', "Scan attempt for username: $username", 'medium');
        }
    }
}
}

// Initialize all security modules
add_action('plugins_loaded', function() {
    DPS_XMLRPC_Blocker::init();
    DPS_Request_Validator::init();
    DPS_User_Agent_Filter::init();
    DPS_Login_Scanner::init();
}, 5);
?>

<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get current settings
$freepik_api_key = get_option('sifp_freepik_api_key', '');
$pexels_api_key = get_option('sifp_pexels_api_key', '');
$unsplash_api_key = get_option('sifp_unsplash_api_key', '');
$default_quality = get_option('sifp_default_quality', 80);
$auto_webp = get_option('sifp_auto_webp', 'yes');
$auto_optimize = get_option('sifp_auto_optimize', 'yes');
$max_file_size = get_option('sifp_max_file_size', 100);
$ai_alt_text = get_option('sifp_ai_alt_text', 'yes');

// Get usage stats (mock data for now - in production, track actual usage)
$total_downloads = get_option('sifp_total_downloads', 0);
$total_saved = get_option('sifp_total_saved_bytes', 0);
?>

<div class="wrap sifp-admin-wrap">
    <div class="sifp-admin-header">
        <div class="sifp-admin-logo">
            <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                <rect width="40" height="40" rx="8" fill="#6366f1"/>
                <path d="M12 20l6 6 10-12" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <div>
                <h1><?php esc_html_e('Stock Image Fetcher Pro', 'stock-image-fetcher-pro'); ?></h1>
                <p><?php esc_html_e('Advanced stock image integration for WordPress', 'stock-image-fetcher-pro'); ?></p>
            </div>
        </div>
        <div class="sifp-admin-version">
            <span class="sifp-badge">v2.0.0</span>
        </div>
    </div>

    <?php if (isset($_GET['settings-updated'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Settings saved successfully!', 'stock-image-fetcher-pro'); ?></p>
        </div>
    <?php endif; ?>

    <div class="sifp-admin-dashboard">
        
        <!-- Stats Cards -->
        <div class="sifp-stats-grid">
            <div class="sifp-stat-card">
                <div class="sifp-stat-icon" style="background: #f0f9ff; color: #0284c7;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M4 16l4-4 4 4 8-8M20 8h-6M20 8v6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="sifp-stat-content">
                    <h3><?php echo number_format($total_downloads); ?></h3>
                    <p><?php esc_html_e('Images Downloaded', 'stock-image-fetcher-pro'); ?></p>
                </div>
            </div>

            <div class="sifp-stat-card">
                <div class="sifp-stat-icon" style="background: #f0fdf4; color: #16a34a;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="sifp-stat-content">
                    <h3><?php echo size_format($total_saved); ?></h3>
                    <p><?php esc_html_e('Storage Saved', 'stock-image-fetcher-pro'); ?></p>
                </div>
            </div>

            <div class="sifp-stat-card">
                <div class="sifp-stat-icon" style="background: #fefce8; color: #ca8a04;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                        <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="sifp-stat-content">
                    <h3>92%</h3>
                    <p><?php esc_html_e('Avg. SEO Score', 'stock-image-fetcher-pro'); ?></p>
                </div>
            </div>

            <div class="sifp-stat-card">
                <div class="sifp-stat-icon" style="background: #fdf2f8; color: #be185d;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M13 10V3L4 14h7v7l9-11h-7z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="sifp-stat-content">
                    <h3>100%</h3>
                    <p><?php esc_html_e('WebP Conversion', 'stock-image-fetcher-pro'); ?></p>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="sifp-admin-content">
            
            <!-- Settings Form -->
            <div class="sifp-admin-section">
                <form method="post" action="options.php" class="sifp-settings-form">
                    <?php settings_fields('stock_image_fetcher_pro_settings'); ?>
                    
                    <!-- API Settings Tab -->
                    <div class="sifp-settings-card">
                        <div class="sifp-card-header">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M10 2a8 8 0 100 16 8 8 0 000-16z" stroke="currentColor" stroke-width="2"/>
                                <path d="M10 6v4l3 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <h2><?php esc_html_e('API Configuration', 'stock-image-fetcher-pro'); ?></h2>
                        </div>

                        <div class="sifp-settings-group">
                            <label class="sifp-settings-label">
                                <?php esc_html_e('Freepik API Key', 'stock-image-fetcher-pro'); ?>
                            </label>
                            <div class="sifp-input-group">
                                <input 
                                    type="text" 
                                    name="sifp_freepik_api_key" 
                                    value="<?php echo esc_attr($freepik_api_key); ?>" 
                                    class="sifp-input" 
                                    placeholder="<?php esc_attr_e('Enter Freepik API key', 'stock-image-fetcher-pro'); ?>"
                                />
                                <button type="button" class="sifp-btn sifp-btn-secondary sifp-test-api" data-source="freepik">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M8 2v12M2 8h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    <?php esc_html_e('Test Connection', 'stock-image-fetcher-pro'); ?>
                                </button>
                            </div>
                            <p class="sifp-help-text">
                                <?php 
                                printf(
                                    esc_html__('Get your free API key from %s.', 'stock-image-fetcher-pro'),
                                    '<a href="https://developer.freepik.com/" target="_blank" rel="noopener">Freepik Developer Portal</a>'
                                );
                                ?>
                            </p>
                        </div>

                        <div class="sifp-settings-group">
                            <label class="sifp-settings-label">
                                <?php esc_html_e('Pexels API Key', 'stock-image-fetcher-pro'); ?>
                            </label>
                            <div class="sifp-input-group">
                                <input 
                                    type="text" 
                                    name="sifp_pexels_api_key" 
                                    value="<?php echo esc_attr($pexels_api_key); ?>" 
                                    class="sifp-input" 
                                    placeholder="<?php esc_attr_e('Enter Pexels API key', 'stock-image-fetcher-pro'); ?>"
                                />
                                <button type="button" class="sifp-btn sifp-btn-secondary sifp-test-api" data-source="pexels">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M8 2v12M2 8h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    <?php esc_html_e('Test Connection', 'stock-image-fetcher-pro'); ?>
                                </button>
                            </div>
                            <p class="sifp-help-text">
                                <?php 
                                printf(
                                    esc_html__('Get your API key from %s.', 'stock-image-fetcher-pro'),
                                    '<a href="https://www.pexels.com/api/" target="_blank" rel="noopener">Pexels API</a>'
                                );
                                ?>
                            </p>
                        </div>

                        <div class="sifp-settings-group">
                            <label class="sifp-settings-label">
                                <?php esc_html_e('Unsplash Access Key', 'stock-image-fetcher-pro'); ?>
                            </label>
                            <div class="sifp-input-group">
                                <input 
                                    type="text" 
                                    name="sifp_unsplash_api_key" 
                                    value="<?php echo esc_attr($unsplash_api_key); ?>" 
                                    class="sifp-input" 
                                    placeholder="<?php esc_attr_e('Enter Unsplash Access Key', 'stock-image-fetcher-pro'); ?>"
                                />
                                <button type="button" class="sifp-btn sifp-btn-secondary sifp-test-api" data-source="unsplash">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M8 2v12M2 8h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    <?php esc_html_e('Test Connection', 'stock-image-fetcher-pro'); ?>
                                </button>
                            </div>
                            <p class="sifp-help-text">
                                <?php 
                                printf(
                                    esc_html__('Get your Access Key from %s.', 'stock-image-fetcher-pro'),
                                    '<a href="https://unsplash.com/developers" target="_blank" rel="noopener">Unsplash Developers</a>'
                                );
                                ?>
                            </p>
                            <div id="sifp-api-status" class="sifp-status-message" style="display:none;"></div>
                        </div>

                        <!-- System Check -->
                        <div class="sifp-settings-group">
                            <label class="sifp-settings-label">
                                <?php esc_html_e('System Compatibility', 'stock-image-fetcher-pro'); ?>
                            </label>
                            <div class="sifp-system-status">
                                <div class="sifp-status-item <?php echo function_exists('imagecreatefromjpeg') ? 'success' : 'error'; ?>">
                                    <span class="sifp-dot"></span>
                                    GD Library: <?php echo function_exists('imagecreatefromjpeg') ? 'Enabled' : 'Disabled'; ?>
                                </div>
                                <div class="sifp-status-item <?php echo function_exists('imagewebp') ? 'success' : 'error'; ?>">
                                    <span class="sifp-dot"></span>
                                    WebP Support (imagewebp): <?php echo function_exists('imagewebp') ? 'Enabled' : 'Disabled'; ?>
                                </div>
                            </div>
                            <?php if (!function_exists('imagewebp')): ?>
                                <p class="sifp-help-text sifp-warning-text">
                                    <svg width="12" height="12" viewBox="0 0 12 12" style="vertical-align: middle;"><path d="M6 1v6M6 9h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                    <?php esc_html_e('WebP support is missing on your server. Compression will fallback to JPEG.', 'stock-image-fetcher-pro'); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Optimization Settings -->
                    <div class="sifp-settings-card">
                        <div class="sifp-card-header">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M10 2v16M2 10h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <circle cx="10" cy="10" r="3" fill="currentColor"/>
                            </svg>
                            <h2><?php esc_html_e('Default Optimization Settings', 'stock-image-fetcher-pro'); ?></h2>
                        </div>

                        <div class="sifp-settings-group">
                            <label class="sifp-settings-label">
                                <?php esc_html_e('Default Compression Quality', 'stock-image-fetcher-pro'); ?>
                            </label>
                            <div class="sifp-slider-container">
                                <input 
                                    type="range" 
                                    name="sifp_default_quality" 
                                    value="<?php echo esc_attr($default_quality); ?>" 
                                    min="60" 
                                    max="100" 
                                    class="sifp-slider"
                                    id="sifp-quality-slider"
                                />
                                <div class="sifp-slider-value">
                                    <span id="sifp-quality-value"><?php echo esc_html($default_quality); ?></span>%
                                </div>
                            </div>
                            <p class="sifp-help-text">
                                <?php esc_html_e('Higher values preserve more quality but result in larger files. 80% is recommended.', 'stock-image-fetcher-pro'); ?>
                            </p>
                        </div>

                        <div class="sifp-settings-group">
                            <label class="sifp-settings-label">
                                <?php esc_html_e('Maximum File Size (KB)', 'stock-image-fetcher-pro'); ?>
                            </label>
                            <input 
                                type="number" 
                                name="sifp_max_file_size" 
                                value="<?php echo esc_attr($max_file_size); ?>" 
                                min="50" 
                                max="1000" 
                                class="sifp-input sifp-input-small"
                            />
                            <p class="sifp-help-text">
                                <?php esc_html_e('Images will be compressed to stay under this size. 100KB is ideal for web performance.', 'stock-image-fetcher-pro'); ?>
                            </p>
                        </div>

                        <div class="sifp-settings-group">
                            <label class="sifp-toggle-label">
                                <input 
                                    type="checkbox" 
                                    name="sifp_auto_optimize" 
                                    value="yes" 
                                    <?php checked($auto_optimize, 'yes'); ?>
                                    class="sifp-toggle-input"
                                />
                                <span class="sifp-toggle-switch"></span>
                                <div>
                                    <strong><?php esc_html_e('Auto-optimize images', 'stock-image-fetcher-pro'); ?></strong>
                                    <p><?php esc_html_e('Automatically compress images on download', 'stock-image-fetcher-pro'); ?></p>
                                </div>
                            </label>
                        </div>

                        <div class="sifp-settings-group">
                            <label class="sifp-toggle-label">
                                <input 
                                    type="checkbox" 
                                    name="sifp_auto_webp" 
                                    value="yes" 
                                    <?php checked($auto_webp, 'yes'); ?>
                                    class="sifp-toggle-input"
                                />
                                <span class="sifp-toggle-switch"></span>
                                <div>
                                    <strong><?php esc_html_e('Convert to WebP', 'stock-image-fetcher-pro'); ?></strong>
                                    <p><?php esc_html_e('Use modern WebP format for better compression', 'stock-image-fetcher-pro'); ?></p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- SEO Settings -->
                    <div class="sifp-settings-card">
                        <div class="sifp-card-header">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M10 2l2 4 4 1-3 3 1 4-4-2-4 2 1-4-3-3 4-1z" fill="currentColor"/>
                            </svg>
                            <h2><?php esc_html_e('SEO & AI Features', 'stock-image-fetcher-pro'); ?></h2>
                        </div>

                        <div class="sifp-settings-group">
                            <label class="sifp-toggle-label">
                                <input 
                                    type="checkbox" 
                                    name="sifp_ai_alt_text" 
                                    value="yes" 
                                    <?php checked($ai_alt_text, 'yes'); ?>
                                    class="sifp-toggle-input"
                                />
                                <span class="sifp-toggle-switch"></span>
                                <div>
                                    <strong><?php esc_html_e('AI-powered alt text generation', 'stock-image-fetcher-pro'); ?></strong>
                                    <p><?php esc_html_e('Automatically generate SEO-friendly alt text', 'stock-image-fetcher-pro'); ?></p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <?php submit_button(__('Save All Settings', 'stock-image-fetcher-pro'), 'primary sifp-btn sifp-btn-primary'); ?>
                </form>
            </div>

            <!-- Help & Documentation -->
            <div class="sifp-admin-sidebar">
                <div class="sifp-help-card">
                    <div class="sifp-help-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                            <path d="M12 16h.01M12 8v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <h3><?php esc_html_e('Quick Start Guide', 'stock-image-fetcher-pro'); ?></h3>
                    <ol class="sifp-help-list">
                        <li><?php esc_html_e('Get your Freepik API key', 'stock-image-fetcher-pro'); ?></li>
                        <li><?php esc_html_e('Enter it above and test connection', 'stock-image-fetcher-pro'); ?></li>
                        <li><?php esc_html_e('Edit any page with Elementor', 'stock-image-fetcher-pro'); ?></li>
                        <li><?php esc_html_e('Add "Stock Image Fetcher Pro" widget', 'stock-image-fetcher-pro'); ?></li>
                        <li><?php esc_html_e('Search and insert optimized images!', 'stock-image-fetcher-pro'); ?></li>
                    </ol>
                </div>

                <div class="sifp-help-card">
                    <h3><?php esc_html_e('Features', 'stock-image-fetcher-pro'); ?></h3>
                    <ul class="sifp-feature-list">
                        <li>
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M4 8l2 2 6-6" stroke="#10b981" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <?php esc_html_e('Unlimited stock image search', 'stock-image-fetcher-pro'); ?>
                        </li>
                        <li>
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M4 8l2 2 6-6" stroke="#10b981" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <?php esc_html_e('Auto-optimize to < 100KB', 'stock-image-fetcher-pro'); ?>
                        </li>
                        <li>
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M4 8l2 2 6-6" stroke="#10b981" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <?php esc_html_e('WebP conversion', 'stock-image-fetcher-pro'); ?>
                        </li>
                        <li>
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M4 8l2 2 6-6" stroke="#10b981" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <?php esc_html_e('SEO score calculator', 'stock-image-fetcher-pro'); ?>
                        </li>
                        <li>
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M4 8l2 2 6-6" stroke="#10b981" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <?php esc_html_e('AI alt text generation', 'stock-image-fetcher-pro'); ?>
                        </li>
                    </ul>
                </div>

                <div class="sifp-help-card sifp-upgrade-card">
                    <div class="sifp-upgrade-icon">⚡</div>
                    <h3><?php esc_html_e('Need Support?', 'stock-image-fetcher-pro'); ?></h3>
                    <p><?php esc_html_e('Having issues? We\'re here to help!', 'stock-image-fetcher-pro'); ?></p>
                    <a href="mailto:support@antigravity.com" class="sifp-btn sifp-btn-outline">
                        <?php esc_html_e('Contact Support', 'stock-image-fetcher-pro'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

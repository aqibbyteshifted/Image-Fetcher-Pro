<?php
namespace Stock_Image_Fetcher_Pro;

if (!defined('ABSPATH')) {
    exit;
}

class Control extends \Elementor\Base_Control
{

    public function get_type()
    {
        return 'stock_image_fetcher_pro_control';
    }

    public function enqueue()
    {
        // Assets are already enqueued in the main plugin file
    }

    public function content_template()
    {
        ?>
        <div class="sifp-control-wrapper">
            <div class="elementor-control-input-wrapper">

                <!-- Unified Search & Source Group -->
                <div class="sifp-search-group">
                    <div class="sifp-input-cluster">
                        <select class="sifp-source-input">
                            <option value="freepik">Freepik</option>
                            <option value="pexels">Pexels</option>
                            <option value="unsplash">Unsplash</option>
                        </select>
                        <div class="sifp-search-field-wrapper">
                            <input type="text" class="sifp-search-input"
                                placeholder="<?php esc_attr_e('Find your next image...', 'stock-image-fetcher-pro'); ?>" />
                            <button type="button" class="sifp-clear-search" style="display:none;">
                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                    <path d="M4 4l8 8M12 4l-8 8" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Advanced Filter Chips -->
                    <div class="sifp-chip-bar">
                        <!-- Sort Chip -->
                        <div class="sifp-filter-chip" data-filter="order_by">
                            <span class="sifp-chip-label">Sort: Relevant</span>
                            <svg class="sifp-chip-arrow" width="10" height="6" viewBox="0 0 10 6" fill="none">
                                <path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <div class="sifp-chip-dropdown">
                                <div class="sifp-dropdown-item active" data-value="relevant">Relevant</div>
                                <div class="sifp-dropdown-item" data-value="latest">Newest</div>
                            </div>
                        </div>

                        <!-- Orientation Chip -->
                        <div class="sifp-filter-chip" data-filter="orientation">
                            <span class="sifp-chip-label">Any Orientation</span>
                            <svg class="sifp-chip-arrow" width="10" height="6" viewBox="0 0 10 6" fill="none">
                                <path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <div class="sifp-chip-dropdown">
                                <div class="sifp-dropdown-item active" data-value="all">Any Orientation</div>
                                <div class="sifp-dropdown-item" data-value="landscape">Horizontal</div>
                                <div class="sifp-dropdown-item" data-value="portrait">Vertical</div>
                                <div class="sifp-dropdown-item" data-value="squarish">Square</div>
                            </div>
                        </div>

                        <!-- Color Chip -->
                        <div class="sifp-filter-chip" data-filter="color">
                            <span class="sifp-chip-label">Any Color</span>
                            <svg class="sifp-chip-arrow" width="10" height="6" viewBox="0 0 10 6" fill="none">
                                <path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <div class="sifp-chip-dropdown color-grid-dropdown">
                                <div class="sifp-dropdown-item active" data-value="all">Any Color</div>
                                <div class="sifp-color-grid">
                                    <div class="sifp-color-dot" data-value="white" style="background: #ffffff; border: 1px solid #ddd;" title="White"></div>
                                    <div class="sifp-color-dot" data-value="black" style="background: #000000;" title="Black"></div>
                                    <div class="sifp-color-dot" data-value="grey" style="background: #808080;" title="Grey"></div>
                                    <div class="sifp-color-dot" data-value="red" style="background: #ff0000;" title="Red"></div>
                                    <div class="sifp-color-dot" data-value="orange" style="background: #ffa500;" title="Orange"></div>
                                    <div class="sifp-color-dot" data-value="yellow" style="background: #ffff00;" title="Yellow"></div>
                                    <div class="sifp-color-dot" data-value="green" style="background: #008000;" title="Green"></div>
                                    <div class="sifp-color-dot" data-value="teal" style="background: #008080;" title="Teal"></div>
                                    <div class="sifp-color-dot" data-value="blue" style="background: #0000ff;" title="Blue"></div>
                                    <div class="sifp-color-dot" data-value="purple" style="background: #800080;" title="Purple"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="sifp-search-btn">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                            <path d="M8 14A6 6 0 1 0 8 2a6 6 0 0 0 0 12zM16 16l-3.5-3.5" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" />
                        </svg>
                        <span><?php esc_html_e('Search Images', 'stock-image-fetcher-pro'); ?></span>
                    </button>
                </div>

                <!-- Results Grid with Skeleton Loader -->
                <div class="sifp-results-container">
                    <div class="sifp-results-header" style="display:none;">
                        <span class="sifp-results-count"></span>
                        <button type="button" class="sifp-toggle-filters">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M2 4h12M4 8h8M6 12h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            </svg>
                            <?php esc_html_e('Filters', 'stock-image-fetcher-pro'); ?>
                        </button>
                    </div>
                    <div class="sifp-results-grid"></div>
                </div>

                <!-- Enhanced Preview Panel -->
                <div class="sifp-preview-panel" style="display:none;">
                    <div class="sifp-preview-header">
                        <h4><?php esc_html_e('Image Preview & Optimization', 'stock-image-fetcher-pro'); ?></h4>
                        <button type="button" class="sifp-close-preview">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M5 5l10 10M15 5L5 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            </svg>
                        </button>
                    </div>

                    <div class="sifp-preview-content">
                        <div class="sifp-preview-image-container">
                            <img class="sifp-preview-img" src="" alt="" />
                            <div class="sifp-image-info">
                                <span class="sifp-dimensions"></span>
                                <span class="sifp-photographer"></span>
                            </div>
                        </div>

                        <!-- SEO Score Card -->
                        <div class="sifp-seo-card">
                            <div class="sifp-seo-header">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="2" />
                                    <path d="M10 6v4l3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                </svg>
                                <h5><?php esc_html_e('SEO Score', 'stock-image-fetcher-pro'); ?></h5>
                            </div>
                            <div class="sifp-seo-score">
                                <div class="sifp-score-circle">
                                    <svg class="sifp-score-ring" width="80" height="80">
                                        <circle cx="40" cy="40" r="34" stroke="#e0e0e0" stroke-width="6" fill="none" />
                                        <circle class="sifp-score-progress" cx="40" cy="40" r="34" stroke="#10b981"
                                            stroke-width="6" fill="none" stroke-dasharray="213.628" stroke-dashoffset="213.628"
                                            transform="rotate(-90 40 40)" />
                                    </svg>
                                    <span class="sifp-score-number">0</span>
                                </div>
                                <div class="sifp-score-details">
                                    <div class="sifp-score-item">
                                        <span
                                            class="sifp-score-label"><?php esc_html_e('Original Size', 'stock-image-fetcher-pro'); ?></span>
                                        <span class="sifp-score-value sifp-size-original">-</span>
                                    </div>
                                    <div class="sifp-score-item">
                                        <span
                                            class="sifp-score-label"><?php esc_html_e('Optimized Size', 'stock-image-fetcher-pro'); ?></span>
                                        <span class="sifp-score-value sifp-size-optimized">-</span>
                                    </div>
                                    <div class="sifp-score-item">
                                        <span
                                            class="sifp-score-label"><?php esc_html_e('Format', 'stock-image-fetcher-pro'); ?></span>
                                        <span class="sifp-score-value sifp-format-value">JPG</span>
                                    </div>
                                    <div class="sifp-score-item">
                                        <span
                                            class="sifp-score-label"><?php esc_html_e('Alt Text', 'stock-image-fetcher-pro'); ?></span>
                                        <span class="sifp-score-value sifp-alt-status">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Optimization Options -->
                        <div class="sifp-optimization-card">
                            <div class="sifp-card-header">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path d="M10 2v16M2 10h16" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                    <circle cx="10" cy="10" r="3" fill="currentColor" />
                                </svg>
                                <h5><?php esc_html_e('Optimization Settings', 'stock-image-fetcher-pro'); ?></h5>
                            </div>

                            <div class="sifp-toggle-option">
                                <label>
                                    <input type="checkbox" class="sifp-optimize-toggle" checked />
                                    <span class="sifp-toggle-slider"></span>
                                </label>
                                <div class="sifp-toggle-label">
                                    <strong><?php esc_html_e('Auto-optimize to < 100KB', 'stock-image-fetcher-pro'); ?></strong>
                                    <small><?php esc_html_e('Compress image while maintaining quality', 'stock-image-fetcher-pro'); ?></small>
                                </div>
                            </div>

                            <div class="sifp-toggle-option">
                                <label>
                                    <input type="checkbox" class="sifp-webp-toggle" checked />
                                    <span class="sifp-toggle-slider"></span>
                                </label>
                                <div class="sifp-toggle-label">
                                    <strong><?php esc_html_e('Convert to WebP', 'stock-image-fetcher-pro'); ?></strong>
                                    <small><?php esc_html_e('Modern format for better performance', 'stock-image-fetcher-pro'); ?></small>
                                </div>
                            </div>

                            <div class="sifp-quality-slider">
                                <label><?php esc_html_e('Compression Quality', 'stock-image-fetcher-pro'); ?></label>
                                <input type="range" class="sifp-quality-input" min="20" max="100" value="80" />
                                <div class="sifp-quality-value">
                                    <span class="sifp-quality-number">80</span>%
                                </div>
                            </div>
                        </div>

                        <!-- SEO Fields -->
                        <div class="sifp-seo-fields">
                            <div class="sifp-field-group">
                                <label class="sifp-field-label">
                                    <?php esc_html_e('Filename', 'stock-image-fetcher-pro'); ?>
                                    <span
                                        class="sifp-field-helper"><?php esc_html_e('SEO-friendly, lowercase, hyphens', 'stock-image-fetcher-pro'); ?></span>
                                </label>
                                <input type="text" class="sifp-filename-input" />
                            </div>

                            <div class="sifp-field-group">
                                <label class="sifp-field-label">
                                    <?php esc_html_e('Alt Text', 'stock-image-fetcher-pro'); ?>
                                    <span
                                        class="sifp-field-helper"><?php esc_html_e('Describe the image for accessibility & SEO', 'stock-image-fetcher-pro'); ?></span>
                                </label>
                                <div class="sifp-alt-input-wrapper">
                                    <textarea class="sifp-alt-input" rows="2"></textarea>
                                    <button type="button" class="sifp-ai-generate-btn"
                                        title="<?php esc_attr_e('Generate with AI', 'stock-image-fetcher-pro'); ?>">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <path d="M8 2l2 4 4 1-3 3 1 4-4-2-4 2 1-4-3-3 4-1z" fill="currentColor" />
                                        </svg>
                                        <?php esc_html_e('AI Generate', 'stock-image-fetcher-pro'); ?>
                                    </button>
                                </div>
                                <div class="sifp-char-count">
                                    <span class="sifp-char-current">0</span> / 125
                                    <?php esc_html_e('characters', 'stock-image-fetcher-pro'); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="sifp-action-buttons">
                            <button type="button" class="sifp-download-btn">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                    <path d="M9 2v10m0 0L5 8m4 4l4-4M2 14v2h14v-2" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" />
                                </svg>
                                <span><?php esc_html_e('Download & Insert', 'stock-image-fetcher-pro'); ?></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Status Messages -->
                <div class="sifp-toast" style="display:none;"></div>

                <!-- Hidden Input for Elementor -->
                <input type="hidden" class="sifp-hidden-input" data-setting="{{ data.name }}" />
            </div>
        </div>
        <?php
    }

    public function get_default_settings()
    {
        return [
            'label_block' => true,
        ];
    }
}

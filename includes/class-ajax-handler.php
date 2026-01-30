<?php
namespace Stock_Image_Fetcher_Pro;

if (!defined('ABSPATH')) {
    exit;
}

class AJAX_Handler
{

    public function init()
    {
        add_action('wp_ajax_sifp_search', [$this, 'search_images']);
        add_action('wp_ajax_sifp_download', [$this, 'download_image']);
        add_action('wp_ajax_sifp_generate_alt', [$this, 'generate_alt_text']);
        add_action('wp_ajax_sifp_test_api', [$this, 'test_api_connection']);
        add_action('wp_ajax_sifp_get_image_info', [$this, 'get_remote_image_info']);
        add_action('wp_ajax_sifp_get_attachment', [$this, 'get_attachment_details']);
    }

    /**
     * Search images from multiple APIs
     */
    public function search_images()
    {
        check_ajax_referer('stock_fetcher_pro_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied.');
        }

        $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';
        $source = isset($_POST['source']) ? sanitize_text_field($_POST['source']) : 'freepik';
        $orientation = isset($_POST['orientation']) ? sanitize_text_field($_POST['orientation']) : '';
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 12;

        switch ($source) {
            case 'pexels':
                $this->search_pexels($keyword, $orientation, $per_page);
                break;
            case 'unsplash':
                $this->search_unsplash($keyword, $orientation, $per_page);
                break;
            case 'freepik':
            default:
                $this->search_freepik($keyword, $orientation, $per_page);
                break;
        }
    }

    /**
     * Search Freepik
     */
    private function search_freepik($keyword, $orientation, $per_page)
    {
        $api_key = get_option('sifp_freepik_api_key');
        if (empty($api_key)) {
            wp_send_json_error('Freepik API Key is missing.');
        }
        
        // Build API URL with correct parameters for Freepik Stock Content API
        $params = [
            'locale' => 'en-US',
            'term' => $keyword,
            'limit' => $per_page,
            'filters[content_type][photo]' => 1
        ];
        
        // Add orientation filter if specified
        if (!empty($orientation) && $orientation !== 'all') {
            $params['filters[orientation][' . $orientation . ']'] = 1;
        }
        
        $api_url = add_query_arg($params, 'https://api.freepik.com/v1/resources');

        $response = wp_remote_get($api_url, [
            'headers' => [
                'X-Freepik-API-Key' => $api_key,
                'Accept-Language' => 'en-US'
            ],
            'timeout' => 15,
        ]);

        // Debug logging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('SIFP Freepik URL: ' . $api_url);
            if (is_wp_error($response)) {
                error_log('SIFP Freepik Error: ' . $response->get_error_message());
            } else {
                error_log('SIFP Freepik Code: ' . wp_remote_retrieve_response_code($response));
                error_log('SIFP Freepik Body: ' . wp_remote_retrieve_body($response));
            }
        }

        if (is_wp_error($response)) {
            wp_send_json_error('Freepik API Request failed: ' . $response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        // Handle API errors
        if ($response_code !== 200) {
            $error_message = 'Freepik API Error (Code: ' . $response_code . ')';
            if (!empty($data['message'])) {
                $error_message .= ': ' . $data['message'];
            } elseif (!empty($data['error'])) {
                $error_message .= ': ' . $data['error'];
            }
            wp_send_json_error($error_message);
        }

        if (empty($data['data'])) {
            wp_send_json_error('No images found on Freepik for keyword: ' . $keyword);
        }

        // Freepik data comes in 'data' key - map to standard format
        $photos = array_map(function ($item) {
            // Get the best available image URL
            $image_url = '';
            $preview_url = '';
            
            if (!empty($item['image']['source']['url'])) {
                $image_url = $item['image']['source']['url'];
            } elseif (!empty($item['thumbnails']['large']['url'])) {
                $image_url = $item['thumbnails']['large']['url'];
            }
            
            if (!empty($item['thumbnails']['medium']['url'])) {
                $preview_url = $item['thumbnails']['medium']['url'];
            } elseif (!empty($item['thumbnails']['small']['url'])) {
                $preview_url = $item['thumbnails']['small']['url'];
            }
            
            return [
                'id' => $item['id'] ?? '',
                'width' => $item['image']['width'] ?? 0,
                'height' => $item['image']['height'] ?? 0,
                'url' => $item['url'] ?? '',
                'photographer' => $item['author']['name'] ?? 'Freepik',
                'photographer_url' => $item['author']['url'] ?? '',
                'alt' => $item['title'] ?? $item['name'] ?? '',
                'src' => [
                    'original' => $image_url,
                    'large' => $image_url,
                    'medium' => $preview_url ?: $image_url,
                    'small' => $preview_url ?: $image_url,
                ]
            ];
        }, $data['data']);

        wp_send_json_success([
            'photos' => $photos,
            'total_results' => $data['meta']['total'] ?? count($photos),
        ]);
    }

    /**
     * Search Pexels
     */
    private function search_pexels($keyword, $orientation, $per_page)
    {
        $api_key = get_option('sifp_pexels_api_key');
        if (empty($api_key)) {
            wp_send_json_error('Pexels API Key is missing.');
        }

        $api_url = "https://api.pexels.com/v1/search?query=" . urlencode($keyword) . "&per_page=" . $per_page;
        if ($orientation && $orientation !== 'all') {
            $api_url .= "&orientation=" . $orientation;
        }

        $response = wp_remote_get($api_url, [
            'headers' => ['Authorization' => $api_key],
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error('Pexels API Request failed.');
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data['photos'])) {
            wp_send_json_error('No images found on Pexels.');
        }

        wp_send_json_success([
            'photos' => $data['photos'],
            'total_results' => $data['total_results'] ?? 0,
        ]);
    }

    /**
     * Search Unsplash
     */
    private function search_unsplash($keyword, $orientation, $per_page)
    {
        $api_key = get_option('sifp_unsplash_api_key');
        if (empty($api_key)) {
            wp_send_json_error('Unsplash Access Key is missing.');
        }

        $api_url = "https://api.unsplash.com/search/photos?query=" . urlencode($keyword) . "&per_page=" . $per_page;
        if ($orientation && $orientation !== 'all') {
            $api_url .= "&orientation=" . $orientation;
        }

        $response = wp_remote_get($api_url, [
            'headers' => ['Authorization' => 'Client-ID ' . $api_key],
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error('Unsplash API Request failed.');
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data['results'])) {
            wp_send_json_error('No images found on Unsplash.');
        }

        $photos = array_map(function ($item) {
            return [
                'id' => $item['id'],
                'width' => $item['width'],
                'height' => $item['height'],
                'url' => $item['links']['html'],
                'photographer' => $item['user']['name'],
                'photographer_url' => $item['user']['links']['html'],
                'alt' => $item['alt_description'] ?? $item['description'] ?? 'Unsplash Image',
                'src' => [
                    'original' => $item['urls']['full'],
                    'large' => $item['urls']['regular'],
                    'medium' => $item['urls']['small'],
                ]
            ];
        }, $data['results']);

        wp_send_json_success([
            'photos' => $photos,
            'total_results' => $data['total'] ?? 0,
        ]);
    }

    /**
     * Download and optimize image
     */
    public function download_image()
    {
        check_ajax_referer('stock_fetcher_pro_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied.');
        }

        $image_url = isset($_POST['image_url']) ? esc_url_raw($_POST['image_url']) : '';
        $filename = isset($_POST['filename']) ? sanitize_file_name($_POST['filename']) : '';
        $alt_text = isset($_POST['alt_text']) ? sanitize_text_field($_POST['alt_text']) : '';
        $photographer = isset($_POST['photographer']) ? sanitize_text_field($_POST['photographer']) : '';
        $photo_url = isset($_POST['photo_url']) ? esc_url_raw($_POST['photo_url']) : '';

        // Optimization settings
        $optimize = isset($_POST['optimize']) && $_POST['optimize'] === 'true';
        $convert_webp = isset($_POST['convert_webp']) && $_POST['convert_webp'] === 'true';
        $quality = isset($_POST['quality']) ? intval($_POST['quality']) : 80;

        if (empty($image_url)) {
            wp_send_json_error('No image URL provided.');
        }

        if (empty($filename)) {
            $filename = 'stock-image-' . time();
        }

        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        // Download the image
        $tmp = download_url($image_url);

        if (is_wp_error($tmp)) {
            wp_send_json_error('Download failed: ' . $tmp->get_error_message());
        }

        // Determine file extension
        $file_ext = 'jpg';
        if ($convert_webp && function_exists('imagewebp')) {
            $file_ext = 'webp';
        }

        // Prepare file array
        $file_array = [
            'name' => $filename . '.' . $file_ext,
            'tmp_name' => $tmp,
        ];

        // Try to optimize image if requested
        if ($optimize || $convert_webp) {
            try {
                $optimizer = new Image_Optimizer();
                $optimized_path = $optimizer->optimize_image($tmp, $quality, $convert_webp);

                // Only use optimized version if it's different and exists
                if ($optimized_path && $optimized_path !== $tmp && file_exists($optimized_path)) {
                    $file_array['tmp_name'] = $optimized_path;
                }
            } catch (\Exception $e) {
                // Log error but continue with original file
                error_log('SIFP: Optimization failed - ' . $e->getMessage());
            }
        }

        // Handle sideload
        $id = media_handle_sideload($file_array, 0);

        // Clean up temporary files
        if (file_exists($tmp)) {
            @unlink($tmp);
        }
        if (isset($optimized_path) && file_exists($optimized_path) && $optimized_path !== $tmp) {
            @unlink($optimized_path);
        }

        if (is_wp_error($id)) {
            wp_send_json_error('Upload failed: ' . $id->get_error_message());
        }

        // Update metadata
        if (!empty($alt_text)) {
            update_post_meta($id, '_wp_attachment_image_alt', $alt_text);
        }

        // Store Freepik attribution
        if (!empty($photographer)) {
            update_post_meta($id, '_sifp_photographer', $photographer);
        }
        if (!empty($photo_url)) {
            update_post_meta($id, '_sifp_source_url', $photo_url);
        }

        // Get file size
        $file_path = get_attached_file($id);
        $file_size = 0;
        $file_size_bytes = 0;
        if ($file_path && file_exists($file_path)) {
            $file_size_bytes = filesize($file_path);
            $file_size = size_format($file_size_bytes);
        }

        // Update stats
        $total_downloads = get_option('sifp_total_downloads', 0);
        update_option('sifp_total_downloads', $total_downloads + 1);

        wp_send_json_success([
            'id' => $id,
            'url' => wp_get_attachment_url($id),
            'file_size' => $file_size,
            'file_size_bytes' => $file_size_bytes,
        ]);
    }

    /**
     * Generate AI alt text (placeholder for future AI integration)
     */
    public function generate_alt_text()
    {
        check_ajax_referer('stock_fetcher_pro_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied.');
        }

        $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';
        $photographer = isset($_POST['photographer']) ? sanitize_text_field($_POST['photographer']) : '';

        // For now, generate a smart alt text based on keyword
        // In future versions, this could integrate with OpenAI Vision API
        $alt_text = $this->generate_smart_alt_text($keyword);

        wp_send_json_success([
            'alt_text' => $alt_text
        ]);
    }

    /**
     * Generate smart alt text based on keyword
     */
    private function generate_smart_alt_text($keyword)
    {
        $keyword = trim($keyword);

        // Capitalize first letter
        $alt_text = ucfirst(strtolower($keyword));

        // Add descriptive prefix if too short
        if (strlen($alt_text) < 20) {
            $prefixes = [
                'Professional',
                'High-quality',
                'Modern',
                'Beautiful',
                'Stunning'
            ];
            $prefix = $prefixes[array_rand($prefixes)];
            $alt_text = $prefix . ' ' . strtolower($alt_text) . ' image';
        }

        return $alt_text;
    }

    /**
     * Test API connection
     */
    public function test_api_connection()
    {
        check_ajax_referer('stock_fetcher_pro_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied.');
        }

        $api_key = isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : '';
        $source = isset($_POST['source']) ? sanitize_text_field($_POST['source']) : 'freepik';

        if (empty($api_key)) {
            wp_send_json_error('API Key is missing.');
        }

        $api_url = '';
        $headers = [];

        switch ($source) {
            case 'pexels':
                $api_url = 'https://api.pexels.com/v1/curated?per_page=1';
                $headers = ['Authorization' => $api_key];
                break;
            case 'unsplash':
                $api_url = 'https://api.unsplash.com/photos?per_page=1';
                $headers = ['Authorization' => 'Client-ID ' . $api_key];
                break;
            case 'freepik':
            default:
                $api_url = 'https://api.freepik.com/v1/resources?locale=en-US&term=test&limit=1&filters[content_type][photo]=1';
                $headers = [
                    'X-Freepik-API-Key' => $api_key,
                    'Accept-Language' => 'en-US'
                ];
                break;
        }

        $response = wp_remote_get($api_url, [
            'headers' => $headers,
            'timeout' => 10,
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error('Connection failed: ' . $response->get_error_message());
        }

        $response_code = wp_remote_retrieve_response_code($response);

        if ($response_code === 200) {
            wp_send_json_success(ucfirst($source) . ' API connection successful!');
        } elseif ($response_code === 401 || $response_code === 403) {
            wp_send_json_error('Invalid ' . $source . ' API key. Please check your key.');
        } else {
            wp_send_json_error($source . ' API returned error code: ' . $response_code);
        }
    }

    /**
     * Get remote image info (size and type)
     */
    public function get_remote_image_info()
    {
        check_ajax_referer('stock_fetcher_pro_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied.');
        }

        $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';

        if (empty($url)) {
            wp_send_json_error('No URL provided.');
        }

        $response = wp_remote_head($url, ['timeout' => 10]);

        if (is_wp_error($response)) {
            wp_send_json_error('Failed to get image info: ' . $response->get_error_message());
        }

        $headers = wp_remote_retrieve_headers($response);
        $size = $headers['content-length'] ?? 0;
        $type = $headers['content-type'] ?? '';

        wp_send_json_success([
            'size_bytes' => (int) $size,
            'size_formatted' => size_format($size),
            'type' => $type
        ]);
    }

    /**
     * Get attachment details by ID
     */
    public function get_attachment_details()
    {
        check_ajax_referer('stock_fetcher_pro_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied.');
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if (!$id || !wp_get_attachment_url($id)) {
            wp_send_json_error('Invalid attachment ID.');
        }

        $file_path = get_attached_file($id);
        $file_size_bytes = 0;
        if ($file_path && file_exists($file_path)) {
            $file_size_bytes = filesize($file_path);
        }

        $mime = get_post_mime_type($id);
        $alt = get_post_meta($id, '_wp_attachment_image_alt', true);
        $photographer = get_post_meta($id, '_sifp_photographer', true);
        $source_url = get_post_meta($id, '_sifp_source_url', true);

        $metadata = wp_get_attachment_metadata($id);

        wp_send_json_success([
            'id' => $id,
            'url' => wp_get_attachment_url($id),
            'actual_size' => $file_size_bytes,
            'actual_type' => $mime,
            'alt' => $alt,
            'photographer' => $photographer,
            'source_url' => $source_url,
            'width' => $metadata['width'] ?? 0,
            'height' => $metadata['height'] ?? 0
        ]);
    }
}

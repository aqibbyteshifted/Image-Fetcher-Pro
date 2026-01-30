<?php
namespace Stock_Image_Fetcher_Pro;

if (!defined('ABSPATH')) {
    exit;
}

class Image_Optimizer
{

    /**
     * Optimize image file
     */
    public function optimize_image($file_path, $quality = 80, $convert_to_webp = false)
    {
        // Check if file exists
        if (!file_exists($file_path)) {
            error_log('SIFP: File not found - ' . $file_path);
            return $file_path; // Return original path as fallback
        }

        // Check if GD library is available
        if (!function_exists('imagecreatefromjpeg')) {
            error_log('SIFP: GD library not available');
            return $file_path; // Return original if GD not available
        }

        // Get image info
        $image_info = @getimagesize($file_path);
        if (!$image_info) {
            error_log('SIFP: Invalid image file - ' . $file_path);
            return $file_path; // Return original if invalid
        }

        $mime_type = $image_info['mime'];
        $width = $image_info[0];
        $height = $image_info[1];

        // Load image based on type
        $image = null;
        try {
            switch ($mime_type) {
                case 'image/jpeg':
                case 'image/jpg':
                    $image = @imagecreatefromjpeg($file_path);
                    break;
                case 'image/png':
                    $image = @imagecreatefrompng($file_path);
                    break;
                case 'image/gif':
                    $image = @imagecreatefromgif($file_path);
                    break;
                case 'image/webp':
                    if (function_exists('imagecreatefromwebp')) {
                        $image = @imagecreatefromwebp($file_path);
                    } else {
                        error_log('SIFP: WebP reading not supported by GD');
                        return $file_path;
                    }
                    break;
                default:
                    error_log('SIFP: Unsupported format - ' . $mime_type);
                    return $file_path;
            }
        } catch (\Exception $e) {
            error_log('SIFP: Error loading image - ' . $e->getMessage());
            return $file_path;
        }

        if (!$image) {
            error_log('SIFP: Failed to create image resource');
            return $file_path;
        }

        // Preserve transparency for PNG
        if ($mime_type === 'image/png') {
            @imagealphablending($image, false);
            @imagesavealpha($image, true);
        }

        // Generate output filename
        $path_info = pathinfo($file_path);
        $output_ext = ($convert_to_webp && function_exists('imagewebp')) ? 'webp' : 'jpg';
        $output_file = $path_info['dirname'] . '/' . $path_info['filename'] . '_optimized.' . $output_ext;

        // Save optimized image
        $success = false;
        try {
            if ($convert_to_webp && function_exists('imagewebp')) {
                $success = @imagewebp($image, $output_file, $quality);
            } else {
                // Fallback to JPEG
                $success = @imagejpeg($image, $output_file, $quality);
            }
        } catch (\Exception $e) {
            error_log('SIFP: Error saving image - ' . $e->getMessage());
            @imagedestroy($image);
            return $file_path;
        }

        @imagedestroy($image);

        if (!$success || !file_exists($output_file)) {
            error_log('SIFP: Failed to save optimized image');
            return $file_path;
        }

        // Check if optimization was successful (file should be smaller or similar size)
        $original_size = filesize($file_path);
        $optimized_size = filesize($output_file);

        // If optimized file is larger and not WebP, use original
        if ($optimized_size > $original_size && !$convert_to_webp) {
            @unlink($output_file);
            return $file_path;
        }

        return $output_file;
    }

    /**
     * Calculate SEO score for an image
     */
    public static function calculate_seo_score($file_size, $has_alt_text, $filename_quality)
    {
        $score = 0;

        // File size score (40 points max)
        if ($file_size < 50000) { // < 50KB
            $score += 40;
        } elseif ($file_size < 100000) { // < 100KB
            $score += 35;
        } elseif ($file_size < 200000) { // < 200KB
            $score += 25;
        } elseif ($file_size < 500000) { // < 500KB
            $score += 15;
        } else {
            $score += 5;
        }

        // Alt text score (30 points max)
        if ($has_alt_text) {
            $score += 30;
        }

        // Filename quality (30 points max)
        $score += $filename_quality;

        return min(100, $score);
    }

    /**
     * Evaluate filename quality for SEO
     */
    public static function evaluate_filename($filename)
    {
        $score = 0;

        // Check if lowercase
        if ($filename === strtolower($filename)) {
            $score += 10;
        }

        // Check if uses hyphens
        if (strpos($filename, '-') !== false) {
            $score += 10;
        }

        // Check length (ideal 3-5 words)
        $word_count = substr_count($filename, '-') + 1;
        if ($word_count >= 3 && $word_count <= 5) {
            $score += 10;
        }

        return $score;
    }
}

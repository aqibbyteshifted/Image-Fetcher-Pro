/**
 * Stock Image Fetcher Pro - Editor JavaScript
 * 2026 Edition with Advanced Features
 */

jQuery(window).on('elementor:init', function () {

    var ControlStockImageFetcherPro = elementor.modules.controls.BaseData.extend({

        onReady: function () {
            // Initialize state BEFORE binding events
            this.currentFilters = {
                orientation: 'all',
                order_by: 'relevant',
                color: 'all'
            };
            this.selectedImage = null;
            this.currentKeyword = '';

            this.initElements();
            this.bindEvents();

            // Handle persistence
            this.handlePersistence();

            // Sync hidden input with initial value
            var value = this.getControlValue();
            if (value) {
                this.elements.$hiddenInput.val(value);
            }
        },

        handlePersistence: function () {
            var imageId = this.getControlValue();
            if (imageId) {
                // If we have an image ID, fetch its metadata and show preview
                this.fetchAttachmentInfo(imageId);
            }
        },

        fetchAttachmentInfo: function (attachmentId) {
            var self = this;
            jQuery.ajax({
                url: stockFetcherProConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'sifp_get_attachment',
                    id: attachmentId,
                    nonce: stockFetcherProConfig.nonce
                },
                success: function (response) {
                    if (response.success) {
                        var photo = {
                            id: response.data.id,
                            src: {
                                original: response.data.source_url || response.data.url,
                                large: response.data.url
                            },
                            width: response.data.width,
                            height: response.data.height,
                            photographer: response.data.photographer,
                            alt: response.data.alt,
                            actual_size: response.data.actual_size,
                            actual_type: response.data.actual_type
                        };
                        self.selectedImage = photo;
                        self.showPreview(photo);
                    }
                }
            });
        },

        initElements: function () {
            this.elements = {
                // Search
                $searchBtn: this.$el.find('.sifp-search-btn'),
                $searchInput: this.$el.find('.sifp-search-input'),
                $clearSearch: this.$el.find('.sifp-clear-search'),

                // Filters (Chips)
                $chipBar: this.$el.find('.sifp-chip-bar'),
                $filterChips: this.$el.find('.sifp-filter-chip'),
                $sourceSelector: this.$el.find('.sifp-source-input'),
                $dropdownItems: this.$el.find('.sifp-dropdown-item'),
                $colorDots: this.$el.find('.sifp-color-dot'),

                // Results
                $resultsContainer: this.$el.find('.sifp-results-container'),
                $resultsHeader: this.$el.find('.sifp-results-header'),
                $resultsCount: this.$el.find('.sifp-results-count'),
                $resultsGrid: this.$el.find('.sifp-results-grid'),

                // Preview
                $previewPanel: this.$el.find('.sifp-preview-panel'),
                $closePreview: this.$el.find('.sifp-close-preview'),
                $previewImg: this.$el.find('.sifp-preview-img'),
                $dimensions: this.$el.find('.sifp-dimensions'),
                $photographer: this.$el.find('.sifp-photographer'),

                // SEO Score
                $scoreNumber: this.$el.find('.sifp-score-number'),
                $scoreProgress: this.$el.find('.sifp-score-progress'),
                $sizeOriginal: this.$el.find('.sifp-size-original'),
                $sizeOptimized: this.$el.find('.sifp-size-optimized'),
                $formatValue: this.$el.find('.sifp-format-value'),
                $altStatus: this.$el.find('.sifp-alt-status'),

                // Optimization
                $optimizeToggle: this.$el.find('.sifp-optimize-toggle'),
                $webpToggle: this.$el.find('.sifp-webp-toggle'),
                $qualityInput: this.$el.find('.sifp-quality-input'),
                $qualityNumber: this.$el.find('.sifp-quality-number'),

                // Fields
                $filenameInput: this.$el.find('.sifp-filename-input'),
                $altInput: this.$el.find('.sifp-alt-input'),
                $charCurrent: this.$el.find('.sifp-char-current'),
                $aiGenerateBtn: this.$el.find('.sifp-ai-generate-btn'),

                // Actions
                $downloadBtn: this.$el.find('.sifp-download-btn'),
                $toast: this.$el.find('.sifp-toast'),
                $hiddenInput: this.$el.find('.sifp-hidden-input')
            };
        },

        bindEvents: function () {
            var self = this;

            // Search events
            this.elements.$searchBtn.on('click', this.onSearchClick.bind(this));
            this.elements.$searchInput.on('input', this.onSearchInput.bind(this));
            this.elements.$searchInput.on('keypress', function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    self.onSearchClick();
                }
            });
            this.elements.$clearSearch.on('click', this.onClearSearch.bind(this));

            // Filter events
            // Filter Chip Toggle
            this.elements.$filterChips.on('click', this.onChipClick.bind(this));

            // Dropdown Item Selection
            this.elements.$dropdownItems.on('click', this.onDropdownItemClick.bind(this));

            // Color Dot Selection
            this.elements.$colorDots.on('click', this.onColorDotClick.bind(this));

            // Close dropdowns on outside click
            jQuery(document).on('click', (e) => {
                if (!jQuery(e.target).closest('.sifp-filter-chip').length) {
                    this.elements.$filterChips.removeClass('open');
                }
            });

            // Results events
            this.elements.$resultsGrid.on('click', '.sifp-result-item', this.onThumbnailClick.bind(this));

            // Preview events
            this.elements.$closePreview.on('click', this.onClosePreview.bind(this));

            // Optimization events
            this.elements.$qualityInput.on('input', this.onQualityChange.bind(this));
            this.elements.$optimizeToggle.on('change', this.updateSEOScore.bind(this));
            this.elements.$webpToggle.on('change', this.updateSEOScore.bind(this));

            // Field events
            this.elements.$altInput.on('input', this.onAltTextInput.bind(this));
            this.elements.$filenameInput.on('input', this.updateSEOScore.bind(this));
            this.elements.$aiGenerateBtn.on('click', this.onGenerateAltText.bind(this));

            // Action events
            this.elements.$downloadBtn.on('click', this.onDownloadClick.bind(this));
        },

        /**
         * Search Input Handler
         */
        onSearchInput: function () {
            var value = this.elements.$searchInput.val();
            if (value.length > 0) {
                this.elements.$clearSearch.show();
            } else {
                this.elements.$clearSearch.hide();
            }
        },

        /**
         * Clear Search
         */
        onClearSearch: function () {
            this.elements.$searchInput.val('').focus();
            this.elements.$clearSearch.hide();
            this.elements.$resultsGrid.empty();
            this.elements.$resultsHeader.hide();
            this.elements.$previewPanel.hide();
        },

        /**
         * Chip Click Handler
         */
        onChipClick: function (e) {
            e.stopPropagation();
            var $chip = jQuery(e.currentTarget);
            var wasOpen = $chip.hasClass('open');

            this.elements.$filterChips.removeClass('open');
            if (!wasOpen) {
                $chip.addClass('open');
            }
        },

        /**
         * Dropdown Item Selection
         */
        onDropdownItemClick: function (e) {
            var $item = jQuery(e.currentTarget);
            var $chip = $item.closest('.sifp-filter-chip');
            var filter = $chip.data('filter');
            var value = $item.data('value');

            $chip.find('.sifp-dropdown-item').removeClass('active');
            $item.addClass('active');

            this.currentFilters[filter] = value;

            // Update UI
            var label = $item.text();
            if (filter === 'order_by') label = 'Sort: ' + label;
            $chip.find('.sifp-chip-label').text(label);

            if (value !== 'all' && value !== 'relevant') {
                $chip.addClass('active');
            } else {
                $chip.removeClass('active');
            }

            $chip.removeClass('open');
            if (this.currentKeyword) this.performSearch(this.currentKeyword);
        },

        /**
         * Color Dot Selection
         */
        onColorDotClick: function (e) {
            var $dot = jQuery(e.currentTarget);
            var $chip = $dot.closest('.sifp-filter-chip');
            var value = $dot.data('value');

            this.elements.$colorDots.removeClass('active');
            $dot.addClass('active');

            this.currentFilters.color = value;
            $chip.find('.sifp-chip-label').text('Color: ' + value.charAt(0).toUpperCase() + value.slice(1));
            $chip.find('.sifp-dropdown-item').removeClass('active');

            $chip.addClass('active');
            $chip.removeClass('open');

            if (this.currentKeyword) this.performSearch(this.currentKeyword);
        },

        /**
         * Search Click Handler
         */
        onSearchClick: function () {
            var keyword = this.elements.$searchInput.val().trim();

            if (!keyword) {
                this.showToast('Please enter a search keyword', 'warning');
                return;
            }

            this.performSearch(keyword);
        },

        /**
         * Perform Search
         */
        performSearch: function (keyword) {
            var self = this;
            this.currentKeyword = keyword;
            var orientation = this.currentFilters.orientation;

            // Show loading
            this.elements.$searchBtn.addClass('loading').prop('disabled', true);
            this.elements.$resultsGrid.html(this.createSkeletonGrid());
            this.elements.$previewPanel.hide();

            jQuery.ajax({
                url: stockFetcherProConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'sifp_search',
                    keyword: keyword,
                    orientation: self.currentFilters.orientation,
                    order_by: self.currentFilters.order_by,
                    color: self.currentFilters.color,
                    source: this.elements.$sourceSelector.val(),
                    per_page: 24,
                    nonce: stockFetcherProConfig.nonce
                },
                success: function (response) {
                    self.elements.$searchBtn.removeClass('loading').prop('disabled', false);

                    if (response.success) {
                        self.renderResults(response.data.photos, keyword);
                        self.elements.$resultsHeader.show();
                        self.elements.$resultsCount.text(
                            response.data.total_results.toLocaleString() + ' results found'
                        );
                    } else {
                        self.elements.$resultsGrid.html(
                            '<div class="sifp-loading error">' + response.data + '</div>'
                        );
                        self.showToast(response.data, 'error');
                    }
                },
                error: function () {
                    self.elements.$searchBtn.removeClass('loading').prop('disabled', false);
                    self.elements.$resultsGrid.html(
                        '<div class="sifp-loading error">Network error. Please try again.</div>'
                    );
                    self.showToast('Network error. Please try again.', 'error');
                }
            });
        },

        /**
         * Create Skeleton Loader Grid
         */
        createSkeletonGrid: function () {
            var html = '';
            for (var i = 0; i < 12; i++) {
                html += '<div class="sifp-result-item"><div class="sifp-skeleton"></div></div>';
            }
            return html;
        },

        /**
         * Render Results Grid
         */
        renderResults: function (photos, keyword) {
            var self = this;
            this.elements.$resultsGrid.empty();

            if (!photos || photos.length === 0) {
                this.elements.$resultsGrid.html(
                    '<div class="sifp-loading">No images found. Try different keywords.</div>'
                );
                return;
            }

            photos.forEach(function (photo, index) {
                var $item = jQuery('<div class="sifp-result-item">')
                    .data('photo', photo)
                    .css('animation-delay', (index * 0.05) + 's');
                $item.html(
                    '<img src="' + photo.src.medium + '" alt="' + photo.alt + '" loading="lazy">' +
                    '<div class="sifp-item-overlay">' +
                    '<div class="sifp-item-photographer">' + photo.photographer + '</div>' +
                    '</div>'
                );

                self.elements.$resultsGrid.append($item);
            });
        },

        /**
         * Thumbnail Click Handler
         */
        onThumbnailClick: function (e) {
            var $item = jQuery(e.currentTarget);
            var photo = $item.data('photo');

            // Update selection
            this.elements.$resultsGrid.find('.sifp-result-item').removeClass('is-selected');
            $item.addClass('is-selected');

            this.selectedImage = photo;
            this.showPreview(photo);
        },

        /**
         * Show Preview Panel
         */
        showPreview: function (photo) {
            var keyword = this.currentKeyword;
            var pageKeyword = this.getPageKeyword();

            // Set image
            this.elements.$previewImg.attr('src', photo.src.large);

            // Generate filename with page keyword
            var baseFilename = photo.alt || keyword || 'image';
            var filename = pageKeyword
                ? this.sanitizeFilename(baseFilename + ' ' + pageKeyword)
                : this.sanitizeFilename(baseFilename);
            this.elements.$filenameInput.val(filename);

            // Generate alt text with page keyword
            var altText = photo.alt || '';
            if (pageKeyword && altText && !altText.toLowerCase().includes(pageKeyword.toLowerCase())) {
                altText = altText + ' - ' + pageKeyword;
            } else if (pageKeyword && !altText) {
                altText = keyword + ' - ' + pageKeyword;
            }
            this.elements.$altInput.val(altText);

            // Set dimensions
            this.elements.$dimensions.text(photo.width + ' × ' + photo.height);

            // Show photogapher
            var sourceName = photo.source ? photo.source.charAt(0).toUpperCase() + photo.source.slice(1) : 'Freepik';
            this.elements.$photographer.html(
                'Photo by <a href="' + photo.photographer_url + '" target="_blank">' + photo.photographer + '</a> on ' + sourceName
            );

            // Generate alt text
            this.updateCharCount();

            // Reset quality slider to 100 for new images
            this.elements.$qualityInput.val(100);
            this.elements.$qualityNumber.text(100);
            this.elements.$optimizeToggle.prop('checked', true); // Enable optimization by default

            // Calculate and show SEO score
            this.updateSEOScore();

            // Fetch actual size info
            this.fetchRemoteFileInfo(photo.src.original);

            // Show panel
            this.elements.$previewPanel.slideDown(400);

            // Scroll to preview
            setTimeout(function () {
                jQuery('html, body').animate({
                    scrollTop: jQuery('.sifp-preview-panel').offset().top - 100
                }, 400);
            }, 100);
        },

        /**
         * Fetch Remote File Information
         */
        fetchRemoteFileInfo: function (url) {
            var self = this;

            // Show loading state in file size if possible or just wait
            this.elements.$sizeOriginal.text('Loading...');

            jQuery.ajax({
                url: stockFetcherProConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'sifp_get_image_info',
                    url: url,
                    nonce: stockFetcherProConfig.nonce
                },
                success: function (response) {
                    if (response.success) {
                        self.selectedImage.actual_size = response.data.size_bytes;
                        self.selectedImage.actual_type = response.data.type;
                        self.updateSEOScore();
                    } else {
                        self.elements.$sizeOriginal.text('Unknown');
                    }
                },
                error: function () {
                    self.elements.$sizeOriginal.text('Error');
                }
            });
        },

        /**
         * Close Preview
         */
        onClosePreview: function () {
            this.elements.$previewPanel.slideUp(300);
            this.elements.$resultsGrid.find('.sifp-result-item').removeClass('is-selected');
            this.selectedImage = null;

            // Clear the image value to remove it from frontend
            this.elements.$hiddenInput.val('');
            this.setValue('');
        },


        /**
         * Get Page Keyword/Title Dynamically
         */
        getPageKeyword: function () {
            try {
                var pageTitle = '';

                // Method 1: Get from Elementor current document
                if (typeof elementor !== 'undefined' && elementor.documents && elementor.documents.getCurrent) {
                    var currentDoc = elementor.documents.getCurrent();
                    if (currentDoc && currentDoc.config && currentDoc.config.title) {
                        pageTitle = currentDoc.config.title;
                    }
                }

                // Method 2: Fallback to elementor config
                if (!pageTitle && typeof elementor !== 'undefined' && elementor.config && elementor.config.document) {
                    pageTitle = elementor.config.document.title || elementor.config.document.post_title || '';
                }

                // Method 3: Fallback to document title
                if (!pageTitle && document.title) {
                    pageTitle = document.title;
                }

                // Clean up the title - remove site name and common suffixes
                if (pageTitle) {
                    pageTitle = pageTitle
                        .replace(/\s*[-–|]\s*.+$/, '') // Remove everything after -, –, or |
                        .trim();
                }

                console.log('SIFP Page Keyword:', pageTitle); // Debug log
                return pageTitle;
            } catch (e) {
                console.log('SIFP: Error getting page keyword:', e);
                return '';
            }
        },

        /**
         * Sanitize Filename
         */
        sanitizeFilename: function (text) {
            return text
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .substring(0, 50);
        },

        /**
         * Quality Change Handler
         */
        onQualityChange: function () {
            var value = this.elements.$qualityInput.val();
            this.elements.$qualityNumber.text(value);
            this.updateSEOScore(); // Trigger dynamic update
        },

        /**
         * Alt Text Input Handler
         */
        onAltTextInput: function () {
            this.updateCharCount();
            this.updateSEOScore();
        },

        /**
         * Update Character Count
         */
        updateCharCount: function () {
            var length = this.elements.$altInput.val().length;
            this.elements.$charCurrent.text(length);

            // Change color based on length
            if (length > 125) {
                this.elements.$charCurrent.css('color', 'var(--sifp-error)');
            } else if (length > 100) {
                this.elements.$charCurrent.css('color', 'var(--sifp-warning)');
            } else {
                this.elements.$charCurrent.css('color', 'var(--sifp-gray-700)');
            }
        },

        /**
         * Generate AI Alt Text
         */
        onGenerateAltText: function () {
            var self = this;
            var keyword = this.currentKeyword;
            var photographer = this.selectedImage ? this.selectedImage.photographer : '';

            this.elements.$aiGenerateBtn.prop('disabled', true).text('Generating...');

            jQuery.ajax({
                url: stockFetcherProConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'sifp_generate_alt',
                    keyword: keyword,
                    photographer: photographer,
                    nonce: stockFetcherProConfig.nonce
                },
                success: function (response) {
                    self.elements.$aiGenerateBtn.prop('disabled', false).html(
                        '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 2l2 4 4 1-3 3 1 4-4-2-4 2 1-4-3-3 4-1z" fill="currentColor"/></svg> AI Generate'
                    );

                    if (response.success) {
                        self.elements.$altInput.val(response.data.alt_text);
                        self.updateCharCount();
                        self.updateSEOScore();
                        self.showToast('Alt text generated successfully!', 'success');
                    }
                },
                error: function () {
                    self.elements.$aiGenerateBtn.prop('disabled', false).html(
                        '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 2l2 4 4 1-3 3 1 4-4-2-4 2 1-4-3-3 4-1z" fill="currentColor"/></svg> AI Generate'
                    );
                }
            });
        },

        /**
         * Update SEO Score
         */
        updateSEOScore: function () {
            if (!this.selectedImage) return;

            var altText = this.elements.$altInput.val();
            var filename = this.elements.$filenameInput.val();
            var convertWebP = this.elements.$webpToggle.is(':checked');
            var isOptimizing = this.elements.$optimizeToggle.is(':checked');

            // Calculate file size score
            // Use actual size if available, otherwise fallback to a default estimate
            var originalSize = this.selectedImage.actual_size || 500000; // 500KB default if unknown
            var estimatedSize = originalSize;

            if (isOptimizing) {
                var quality = parseInt(this.elements.$qualityInput.val()) || 100;

                // Heuristic tuned to hit < 100KB:
                // Large images compress non-linearly. we use a power curve.
                var power = convertWebP ? 1.5 : 2.0;
                var ratio = Math.pow(quality / 100, power);

                if (convertWebP) {
                    ratio = ratio * 0.7; // WebP is significantly more efficient
                }

                estimatedSize = originalSize * ratio;

                // Ensure it doesn't show 0
                if (estimatedSize < 5000) estimatedSize = 5000;
            } else if (convertWebP) {
                estimatedSize = originalSize * 0.8;
            }

            var fileSizeScore = 0;
            if (estimatedSize < 100000) fileSizeScore = 40;
            else if (estimatedSize < 200000) fileSizeScore = 30;
            else if (estimatedSize < 500000) fileSizeScore = 20;
            else fileSizeScore = 10;

            // Alt text score
            var altScore = altText.length > 10 ? 30 : 0;

            // Filename score
            var filenameScore = this.evaluateFilename(filename);

            // Total score
            var totalScore = Math.min(100, fileSizeScore + altScore + filenameScore);

            // Update UI
            this.animateScore(totalScore);

            // Update score details
            var originalSizeText = this.formatSize(originalSize);
            var estimatedSizeText = this.formatSize(estimatedSize);

            this.elements.$sizeOriginal.text(originalSizeText);
            this.elements.$sizeOptimized.text((isOptimizing ? '~' : '') + estimatedSizeText);

            // Update Format
            var format = 'JPG';
            if (convertWebP) {
                format = 'WebP';
            } else if (this.selectedImage.actual_type) {
                var mime = this.selectedImage.actual_type.split('/')[1];
                format = mime ? mime.toUpperCase() : 'JPG';
            }
            this.elements.$formatValue.text(format);

            var altStatus = this.elements.$altStatus;
            if (altText.length > 10) {
                altStatus.text('Good').removeClass('missing').addClass('good');
            } else {
                altStatus.text('Missing').removeClass('good').addClass('missing');
            }
        },

        formatSize: function (bytes) {
            if (bytes > 1024 * 1024) {
                return (bytes / (1024 * 1024)).toFixed(1) + 'MB';
            }
            return Math.round(bytes / 1024) + 'KB';
        },

        /**
         * Animate SEO Score
         */
        animateScore: function (score) {
            var self = this;
            var currentScore = parseInt(this.elements.$scoreNumber.text()) || 0;
            var circumference = 2 * Math.PI * 34; // r=34
            var offset = circumference - (score / 100) * circumference;

            // Animate number
            jQuery({ score: currentScore }).animate({ score: score }, {
                duration: 1000,
                easing: 'swing',
                step: function (now) {
                    self.elements.$scoreNumber.text(Math.round(now));
                }
            });

            // Animate progress ring
            this.elements.$scoreProgress.css({
                'stroke-dashoffset': offset,
                'stroke': this.getScoreColor(score)
            });
        },

        /**
         * Get Score Color
         */
        getScoreColor: function (score) {
            if (score >= 80) return '#10b981';
            if (score >= 60) return '#f59e0b';
            return '#ef4444';
        },

        /**
         * Evaluate Filename Quality
         */
        evaluateFilename: function (filename) {
            var score = 0;

            // Lowercase check
            if (filename === filename.toLowerCase()) score += 10;

            // Hyphen check
            if (filename.includes('-')) score += 10;

            // Word count (3-5 ideal)
            var wordCount = filename.split('-').length;
            if (wordCount >= 3 && wordCount <= 5) score += 10;

            return score;
        },

        /**
         * Download & Insert Image
         */
        onDownloadClick: function () {
            var self = this;

            if (!this.selectedImage) {
                this.showToast('Please select an image first', 'warning');
                return;
            }

            var downloadData = {
                action: 'sifp_download',
                image_url: this.selectedImage.src.original,
                filename: this.elements.$filenameInput.val(),
                alt_text: this.elements.$altInput.val(),
                photographer: this.selectedImage.photographer,
                photo_url: this.selectedImage.url,
                optimize: this.elements.$optimizeToggle.is(':checked'),
                convert_webp: this.elements.$webpToggle.is(':checked'),
                quality: this.elements.$qualityInput.val(),
                nonce: stockFetcherProConfig.nonce
            };

            // Update button state
            this.elements.$downloadBtn
                .prop('disabled', true)
                .html('<svg width="18" height="18" viewBox="0 0 18 18" fill="none" class="spin"><circle cx="9" cy="9" r="7" stroke="currentColor" stroke-width="2" fill="none" opacity="0.3"/><path d="M9 2a7 7 0 017 7" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/></svg><span>Downloading...</span>');

            jQuery.ajax({
                url: stockFetcherProConfig.ajaxUrl,
                type: 'POST',
                data: downloadData,
                success: function (response) {
                    self.elements.$downloadBtn
                        .prop('disabled', false)
                        .html('<svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M9 2v10m0 0L5 8m4 4l4-4M2 14v2h14v-2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg><span>Download & Insert</span>');

                    if (response.success) {
                        // Update hidden input
                        self.setValue(response.data.id);
                        self.elements.$hiddenInput.val(response.data.id).trigger('change');

                        // Update score with actual file size
                        if (response.data.file_size_bytes) {
                            self.selectedImage.actual_size = response.data.file_size_bytes;
                            self.updateSEOScore();
                        }

                        // Refresh Elementor Preview
                        if (window.elementor) {
                            elementor.saver.update();
                        }

                        // Success toast
                        self.showToast(
                            'Image updated! (' + response.data.file_size + ')',
                            'success'
                        );
                    } else {
                        self.showToast(response.data, 'error');
                    }
                },
                error: function () {
                    self.elements.$downloadBtn
                        .prop('disabled', false)
                        .html('<svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M9 2v10m0 0L5 8m4 4l4-4M2 14v2h14v-2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg><span>Download & Insert</span>');

                    self.showToast('Network error during download', 'error');
                }
            });
        },

        /**
         * Show Toast Notification
         */
        showToast: function (message, type) {
            var self = this;
            type = type || 'success';

            var icon = '';
            if (type === 'success') {
                icon = '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="2"/><path d="M6 10l2 2 6-6" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/></svg>';
            } else if (type === 'error') {
                icon = '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="2"/><path d="M10 6v4M10 14h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
            } else if (type === 'warning') {
                icon = '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 2L2 18h16L10 2z" stroke="currentColor" stroke-width="2"/><path d="M10 8v4M10 15h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
            }

            this.elements.$toast
                .html(icon + '<span>' + message + '</span>')
                .removeClass('success error warning')
                .addClass(type)
                .fadeIn(300);

            setTimeout(function () {
                self.elements.$toast.fadeOut(300);
            }, 4000);
        },

        /**
         * Cleanup on destroy
         */
        onBeforeDestroy: function () {
            this.elements.$searchBtn.off();
            this.elements.$searchInput.off();
            this.elements.$clearSearch.off();
            this.elements.$toggleFilters.off();
            this.elements.$filterBtns.off();
            this.elements.$resultsGrid.off();
            this.elements.$closePreview.off();
            this.elements.$qualityInput.off();
            this.elements.$altInput.off();
            this.elements.$aiGenerateBtn.off();
            this.elements.$downloadBtn.off();
        }
    });

    // Register control
    elementor.addControlView('stock_image_fetcher_pro_control', ControlStockImageFetcherPro);
});

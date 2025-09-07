/**
 * Professional Single Deal Page JavaScript
 * Optimized for performance and user experience
 */

(function($) {
    'use strict';

    // Main object for deal functionality
    window.btDeals = {
        
        // Initialize all functionality
        init: function() {
            this.setupSocialShare();
            this.setupCouponReveal();
            this.loadSimilarDeals();
            this.setupCarousel();
            this.trackPageView();
            this.lazyLoadImages();
        },

        // Social sharing functionality
        setupSocialShare: function() {
            $('.bt-share-btn').on('click', function(e) {
                e.preventDefault();
                const shareType = $(this).data('share');
                const url = btDealsAjax.shareUrl;
                const title = btDealsAjax.shareTitle;
                const text = btDealsAjax.shareText;

                btDeals.handleShare(shareType, url, title, text);
            });
        },

        // Handle different share types
        handleShare: function(type, url, title, text) {
            let shareUrl = '';
            
            switch(type) {
                case 'facebook':
                    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
                    break;
                case 'twitter':
                    shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`;
                    break;
                case 'whatsapp':
                    shareUrl = `https://wa.me/?text=${encodeURIComponent(title + ' ' + url)}`;
                    break;
                case 'copy':
                    this.copyToClipboard(url);
                    return;
            }

            if (shareUrl) {
                window.open(shareUrl, 'share-popup', 'width=600,height=400,scrollbars=no,resizable=no');
            }
        },

        // Copy URL to clipboard
        copyToClipboard: function(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    this.showNotification('Link copied to clipboard!', 'success');
                }).catch(() => {
                    this.fallbackCopyTextToClipboard(text);
                });
            } else {
                this.fallbackCopyTextToClipboard(text);
            }
        },

        // Fallback copy method
        fallbackCopyTextToClipboard: function(text) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.position = "fixed";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    this.showNotification('Link copied to clipboard!', 'success');
                } else {
                    this.showNotification('Failed to copy link', 'error');
                }
            } catch (err) {
                this.showNotification('Failed to copy link', 'error');
            }

            document.body.removeChild(textArea);
        },

        // Coupon reveal functionality
        setupCouponReveal: function() {
            $('.bt-coupon-reveal').on('click', function(e) {
                e.preventDefault();
                const $btn = $(this);
                const couponCode = $btn.data('coupon');
                
                if ($btn.hasClass('revealed')) return;

                $btn.find('.bt-coupon-text').hide();
                $btn.find('.bt-loading').show();

                // Simulate API call delay for better UX
                setTimeout(() => {
                    $btn.find('.bt-loading').hide();
                    $btn.find('.bt-coupon-text').text(couponCode).show();
                    $btn.addClass('revealed');
                    
                    // Track coupon reveal
                    btDeals.trackEvent('coupon_revealed', {
                        deal_id: btDealsAjax.postId,
                        coupon_code: couponCode
                    });
                }, 800);
            });
        },

        // Load similar deals via AJAX
        loadSimilarDeals: function() {
            const $carousel = $('#similarDealsCarousel');
            
            if (!$carousel.length) return;

            $.ajax({
                url: btDealsAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'bt_get_similar_deals',
                    nonce: btDealsAjax.nonce,
                    post_id: btDealsAjax.postId,
                    store_id: btDealsAjax.storeId
                },
                success: function(response) {
                    if (response.success && response.data) {
                        $carousel.html(response.data);
                        btDeals.initCarouselControls();
                    } else {
                        $carousel.html('<p>No similar deals found.</p>');
                    }
                },
                error: function() {
                    $carousel.html('<p>Failed to load similar deals.</p>');
                }
            });
        },

        // Setup carousel functionality
        setupCarousel: function() {
            // Will be initialized after AJAX load
        },

        // Initialize carousel controls after AJAX load
        initCarouselControls: function() {
            const $container = $('.bt-carousel-container');
            if (!$container.length) return;

            // Add navigation buttons
            const $carousel = $('.bt-deals-carousel');
            $carousel.append(`
                <button class="bt-carousel-prev" aria-label="Previous deals">‹</button>
                <button class="bt-carousel-next" aria-label="Next deals">›</button>
            `);

            let currentIndex = 0;
            const $cards = $('.bt-deal-card');
            const cardWidth = 300; // Card width + gap
            const visibleCards = Math.floor($carousel.width() / cardWidth);
            const maxIndex = Math.max(0, $cards.length - visibleCards);

            // Previous button
            $('.bt-carousel-prev').on('click', function() {
                currentIndex = Math.max(0, currentIndex - 1);
                btDeals.updateCarouselPosition(currentIndex, cardWidth);
            });

            // Next button
            $('.bt-carousel-next').on('click', function() {
                currentIndex = Math.min(maxIndex, currentIndex + 1);
                btDeals.updateCarouselPosition(currentIndex, cardWidth);
            });

            // Touch/swipe support
            let startX = 0;
            let isScrolling = false;

            $container.on('touchstart', function(e) {
                startX = e.originalEvent.touches[0].clientX;
                isScrolling = false;
            });

            $container.on('touchmove', function(e) {
                if (!startX) return;
                
                const currentX = e.originalEvent.touches[0].clientX;
                const diffX = startX - currentX;
                
                if (Math.abs(diffX) > 50 && !isScrolling) {
                    isScrolling = true;
                    if (diffX > 0 && currentIndex < maxIndex) {
                        // Swipe left - next
                        currentIndex++;
                    } else if (diffX < 0 && currentIndex > 0) {
                        // Swipe right - previous
                        currentIndex--;
                    }
                    btDeals.updateCarouselPosition(currentIndex, cardWidth);
                }
            });

            $container.on('touchend', function() {
                startX = 0;
                isScrolling = false;
            });
        },

        // Update carousel position
        updateCarouselPosition: function(index, cardWidth) {
            const $container = $('.bt-carousel-container');
            const translateX = -index * cardWidth;
            $container.css('transform', `translateX(${translateX}px)`);

            // Update button states
            $('.bt-carousel-prev').toggleClass('disabled', index === 0);
            $('.bt-carousel-next').toggleClass('disabled', index >= Math.max(0, $('.bt-deal-card').length - Math.floor($('.bt-deals-carousel').width() / cardWidth)));
        },

        // Track deal click
        trackClick: function(dealId) {
            this.trackEvent('deal_click', {
                deal_id: dealId,
                timestamp: Date.now()
            });
        },

        // Track page view
        trackPageView: function() {
            this.trackEvent('deal_view', {
                deal_id: btDealsAjax.postId,
                timestamp: Date.now()
            });
        },

        // Generic event tracking
        trackEvent: function(eventName, data) {
            // Send to WordPress via AJAX for logging
            $.ajax({
                url: btDealsAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'bt_track_event',
                    nonce: btDealsAjax.nonce,
                    event: eventName,
                    data: JSON.stringify(data)
                }
            });

            // Send to Google Analytics if available
            if (typeof gtag !== 'undefined') {
                gtag('event', eventName, data);
            }

            // Send to Facebook Pixel if available
            if (typeof fbq !== 'undefined') {
                fbq('track', 'ViewContent', data);
            }
        },

        // Show notification to user
        showNotification: function(message, type = 'info') {
            const $notification = $(`
                <div class="bt-notification bt-notification-${type}">
                    <span class="bt-notification-message">${message}</span>
                    <button class="bt-notification-close">×</button>
                </div>
            `);

            // Add to page
            if (!$('.bt-notifications').length) {
                $('body').append('<div class="bt-notifications"></div>');
            }
            $('.bt-notifications').append($notification);

            // Auto remove after 3 seconds
            setTimeout(() => {
                $notification.addClass('bt-notification-fade');
                setTimeout(() => {
                    $notification.remove();
                }, 300);
            }, 3000);

            // Manual close
            $notification.find('.bt-notification-close').on('click', function() {
                $notification.addClass('bt-notification-fade');
                setTimeout(() => {
                    $notification.remove();
                }, 300);
            });
        },

        // Lazy load images for better performance
        lazyLoadImages: function() {
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            imageObserver.unobserve(img);
                        }
                    });
                });

                document.querySelectorAll('img[data-src]').forEach(img => {
                    imageObserver.observe(img);
                });
            }
        },

        // Preload critical resources
        preloadResources: function() {
            // Preload next probable page (similar deals)
            const $similarLinks = $('.bt-deal-card a');
            if ($similarLinks.length > 0) {
                const link = document.createElement('link');
                link.rel = 'prefetch';
                link.href = $similarLinks.first().attr('href');
                document.head.appendChild(link);
            }
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        btDeals.init();
        
        // Preload resources after initial load
        setTimeout(btDeals.preloadResources, 2000);
    });

    // Add notification styles dynamically to avoid blocking CSS
    const notificationCSS = `
        <style>
        .bt-notifications {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            pointer-events: none;
        }
        .bt-notification {
            background: white;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 10px;
            pointer-events: auto;
            opacity: 1;
            transform: translateX(0);
            transition: all 0.3s ease;
            max-width: 300px;
        }
        .bt-notification.bt-notification-fade {
            opacity: 0;
            transform: translateX(100%);
        }
        .bt-notification-success {
            border-left: 4px solid #28a745;
        }
        .bt-notification-error {
            border-left: 4px solid #dc3545;
        }
        .bt-notification-info {
            border-left: 4px solid #17a2b8;
        }
        .bt-notification-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #999;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .bt-carousel-prev,
        .bt-carousel-next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.9);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 20px;
            font-weight: bold;
            color: #333;
            cursor: pointer;
            z-index: 10;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .bt-carousel-prev:hover,
        .bt-carousel-next:hover {
            background: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .bt-carousel-prev {
            left: 10px;
        }
        .bt-carousel-next {
            right: 10px;
        }
        .bt-carousel-prev.disabled,
        .bt-carousel-next.disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }
        @media (max-width: 768px) {
            .bt-notifications {
                right: 10px;
                left: 10px;
            }
            .bt-notification {
                max-width: none;
            }
        }
        </style>
    `;
    
    $('head').append(notificationCSS);

})(jQuery);

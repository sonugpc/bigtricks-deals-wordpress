/**
 * BigTricks Deals Single Page JavaScript v3.1
 * Refactored for performance, maintainability, and improved UX.
 */
(function($) {
    'use strict';

    const DealPage = {
        // Configuration
        config: {
            similarDealsCount: 5,
        },

        // State
        state: {
            isLoading: false,
        },

        // Initialize the script
        init: function() {
            this.bindEvents();
            this.loadSimilarDeals();
        },

        // Bind all event listeners
        bindEvents: function() {
            $('.bt-single-deal-container').on('click', '.bt-coupon-reveal', this.handleCouponReveal.bind(this));
            $('.bt-single-deal-container').on('click', '.bt-share-trigger, .bt-share-trigger-mobile', this.handleShareTrigger.bind(this));
        },

        handleShareTrigger: function(e) {
            e.preventDefault();
            const shareText = `🔥 ${btDealsAjax.shareTitle} at just ${btDealsAjax.sharePrice}! 🤑\n\n${btDealsAjax.shareUrl}`;
            this.showShareModal(btDealsAjax.shareUrl, btDealsAjax.shareTitle, shareText);
        },

        handleCouponReveal: function(e) {
            e.preventDefault();
            const $button = $(e.currentTarget);
            const $code = $button.siblings('.bt-coupon-code');
            const couponCode = $button.data('coupon');

            if ($code.length) {
                $code.text(couponCode).show();
                $button.hide();
                this.copyToClipboard(couponCode);
            }
        },

        // Similar Deals Logic
        loadSimilarDeals: function() {
            if (this.state.isLoading) return;
            this.state.isLoading = true;
            $('.bt-loading-similar').show();

            $.ajax({
                url: btDealsAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'bt_get_similar_deals',
                    nonce: btDealsAjax.nonce,
                    deal_id: btDealsAjax.postId,
                    limit: this.config.similarDealsCount
                },
                success: (response) => {
                    if (response.success && response.data.length > 0) {
                        this.renderSimilarDeals(response.data);
                    } else {
                        console.error('No similar deals found.');
                    }
                },
                error: () => console.error('Failed to load similar deals.'),
                complete: () => {
                    this.state.isLoading = false;
                    $('.bt-loading-similar').hide();
                }
            });
        },

        renderSimilarDeals: function(deals) {
            const $grid = $('#btSimilarDealsGrid');
            $grid.empty();
            deals.forEach((deal, index) => {
                $grid.append(this.createDealItem(deal, index));
            });
        },

        createDealItem: function(deal, index) {
            const imageUrl = deal.thumbnail || 'https://via.placeholder.com/280x160?text=No+Image';
            return `
                <div class="deal-item" style="animation-delay: ${index * 0.1}s">
                    <img src="${imageUrl}" alt="${deal.title}" class="deal-item-image" loading="lazy" width="280" height="160">
                    <h4 class="deal-item-title">${deal.title}</h4>
                    <div class="deal-item-price">₹${deal.sale_price}</div>
                    <div class="deal-item-store">${deal.store_name || 'Store'}</div>
                    <a href="${deal.offer_url}" class="deal-item-btn is-btn" target="_blank" rel="nofollow noopener" data-deal-id="${deal.id}">Get Deal</a>
                </div>
            `;
        },

        // Share Functionality
        showShareModal: function(url, title, text) {
            const modalHtml = `
                <div class="bt-share-modal-overlay">
                    <div class="bt-share-modal">
                        <div class="bt-share-modal-header">
                            <h3>Share this Deal</h3>
                            <button class="bt-share-modal-close">&times;</button>
                        </div>
                        <div class="bt-share-modal-content">
                            <div class="bt-share-buttons-grid">
                                <button class="bt-share-btn-modal" data-platform="facebook"><i class="rbi rbi-facebook"></i> Facebook</button>
                                <button class="bt-share-btn-modal" data-platform="twitter"><i class="rbi rbi-twitter"></i> Twitter</button>
                                <button class="bt-share-btn-modal" data-platform="whatsapp"><i class="rbi rbi-whatsapp"></i> WhatsApp</button>
                                <button class="bt-share-btn-modal" data-platform="telegram"><i class="rbi rbi-telegram"></i> Telegram</button>
                                <button class="bt-share-btn-modal" data-platform="copy"><i class="rbi rbi-link-line"></i> Copy Link</button>
                            </div>
                        </div>
                    </div>
                </div>`;
            $('body').append(modalHtml);

            $('.bt-share-modal-close, .bt-share-modal-overlay').on('click', () => $('.bt-share-modal-overlay').remove());
            $('.bt-share-btn-modal').on('click', (e) => {
                this.shareOnPlatform($(e.currentTarget).data('platform'), url, title, text);
                $('.bt-share-modal-overlay').remove();
            });
        },

        shareOnPlatform: function(platform, url, title, text) {
            const encodedUrl = encodeURIComponent(url);
            let shareUrl = '';
            switch (platform) {
                case 'facebook': shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`; break;
                case 'twitter': shareUrl = `https://twitter.com/intent/tweet?url=${encodedUrl}&text=${encodeURIComponent(title)}`; break;
                case 'whatsapp': shareUrl = `https://wa.me/?text=${encodeURIComponent(text)}%20${encodedUrl}`; break;
                case 'telegram': shareUrl = `https://t.me/share/url?url=${encodedUrl}&text=${encodeURIComponent(text)}`; break;
                case 'copy': this.copyToClipboard(url); return;
            }
            if (shareUrl) window.open(shareUrl, '_blank', 'width=600,height=400');
        },

        // Utilities
        copyToClipboard: function(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).catch(err => {
                    console.error('Could not copy text: ', err);
                });
            }
        },

        padNumber: (num) => num.toString().padStart(2, '0'),
    };

    // Initialize on document ready
    $(document).ready(() => {
        DealPage.init();
    });

})(jQuery);

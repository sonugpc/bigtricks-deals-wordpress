/**
 * BigTricks Deals Single Page JavaScript v3.1
 * Refactored for performance, maintainability, and improved UX.
 */
(function($) {
    'use strict';

    const DealPage = {
        // Initialize the script
        init: function() {
            this.bindEvents();
        },

        // Bind all event listeners
        bindEvents: function() {
            $('.bt-single-deal-container').on('click', '.bt-coupon-reveal', this.handleCouponReveal.bind(this));
            $('.bt-single-deal-container').on('click', '.bt-share-trigger, .bt-share-trigger-mobile', this.handleShareTrigger.bind(this));

            // New social sharing buttons
            $('.bt-single-deal-container').on('click', '.bt-copy-btn', this.handleCopyLink.bind(this));
            $('.bt-single-deal-container').on('click', '.bt-whatsapp-btn', this.handleWhatsAppShare.bind(this));
            $('.bt-single-deal-container').on('click', '.bt-facebook-btn', this.handleFacebookShare.bind(this));
            $('.bt-single-deal-container').on('click', '.bt-twitter-btn', this.handleTwitterShare.bind(this));
            $('.bt-single-deal-container').on('click', '.bt-telegram-btn', this.handleTelegramShare.bind(this));
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

        // New social sharing handlers
        handleCopyLink: function(e) {
            e.preventDefault();
            const url = $(e.currentTarget).data('url');
            this.copyToClipboard(url);
            this.showToast('Link copied to clipboard!');
        },

        handleWhatsAppShare: function(e) {
            e.preventDefault();
            const url = $(e.currentTarget).data('url');
            const title = $(e.currentTarget).data('title');
            const text = `🔥 ${title} - Check out this amazing deal! ${url}`;
            const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(text)}`;
            window.open(whatsappUrl, '_blank', 'width=600,height=400');
        },

        handleFacebookShare: function(e) {
            e.preventDefault();
            const url = $(e.currentTarget).data('url');
            const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
            window.open(facebookUrl, '_blank', 'width=600,height=400');
        },

        handleTwitterShare: function(e) {
            e.preventDefault();
            const url = $(e.currentTarget).data('url');
            const title = $(e.currentTarget).data('title');
            const twitterUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`;
            window.open(twitterUrl, '_blank', 'width=600,height=400');
        },

        handleTelegramShare: function(e) {
            e.preventDefault();
            const url = $(e.currentTarget).data('url');
            const title = $(e.currentTarget).data('title');
            const text = `🔥 ${title} - Check out this amazing deal!`;
            const telegramUrl = `https://t.me/share/url?url=${encodeURIComponent(url)}&text=${encodeURIComponent(text)}`;
            window.open(telegramUrl, '_blank', 'width=600,height=400');
        },

        showToast: function(message) {
            // Simple toast notification
            const toast = $(`<div class="bt-toast">${message}</div>`);
            $('body').append(toast);
            setTimeout(() => {
                toast.remove();
            }, 3000);
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

/**
 * BigTricks Deals Single Page JavaScript v3.0
 * Enhanced UX with countdown timer, wishlist, and modern interactions
 */

(function($) {
	'use strict';

	// Main Deals Object
	window.btDeals = {

		// Configuration
		config: {
			carouselItems: 5,
			carouselItemWidth: 280,
			carouselGap: 24,
			autoPlay: false,
			autoPlayInterval: 5000,
			transitionDuration: 300
		},

		// State
		state: {
			currentSlide: 0,
			totalSlides: 0,
			isLoading: false,
			carouselData: [],
			autoPlayTimer: null
		},

		// Initialize
		init: function() {
			this.bindEvents();
			this.loadSimilarDeals();
			this.initShareButtons();
			this.initCouponReveal();
			this.initCountdownTimer();
			this.initWishlist();
			this.initShareTrigger();
			this.addFadeInAnimations();
		},

		// Bind Events
		bindEvents: function() {
			const self = this;

			// Carousel Navigation
			$(document).on('click', '#btPrevBtn', function(e) {
				e.preventDefault();
				if (!$(this).prop('disabled')) {
					self.prevSlide();
				}
			});

			$(document).on('click', '#btNextBtn', function(e) {
				e.preventDefault();
				if (!$(this).prop('disabled')) {
					self.nextSlide();
				}
			});

			// Carousel Dots
			$(document).on('click', '.bt-dot', function() {
				const slideIndex = $(this).index();
				self.goToSlide(slideIndex);
			});

			// Keyboard Navigation
			$(document).on('keydown', function(e) {
				if (e.key === 'ArrowLeft') {
					self.prevSlide();
				} else if (e.key === 'ArrowRight') {
					self.nextSlide();
				}
			});

			// Touch/Swipe Support
			this.initTouchSupport();

			// Window Resize
			$(window).on('resize', this.debounce(function() {
				self.updateCarouselLayout();
			}, 250));
		},

		// Load Similar Deals
		loadSimilarDeals: function() {
			const self = this;
			const $container = $('#btSimilarCarousel');
			const $loading = $('.bt-loading-similar');

			if (this.state.isLoading) return;

			this.state.isLoading = true;
			$loading.show();

			$.ajax({
				url: btDealsData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'get_similar_deals',
					nonce: btDealsData.nonce,
					deal_id: btDealsData.postId,
					limit: this.config.carouselItems
				},
				success: function(response) {
					if (response.success && response.data.length > 0) {
						self.state.carouselData = response.data;
						self.renderCarousel(response.data);
						self.updateNavigation();
					} else {
						self.showError('No similar deals found');
					}
				},
				error: function() {
					self.showError('Failed to load similar deals');
				},
				complete: function() {
					self.state.isLoading = false;
					$loading.hide();
				}
			});
		},

		// Render Carousel
		renderCarousel: function(deals) {
			const $container = $('#btSimilarCarousel');
			const $carouselContainer = $('<div class="bt-carousel-container"></div>');
			const $dotsContainer = $('#btCarouselDots');

			// Clear existing content
			$container.empty();
			$dotsContainer.empty();

			// Create deal items
			deals.forEach((deal, index) => {
				const $item = this.createCarouselItem(deal, index);
				$carouselContainer.append($item);
			});

			// Create dots
			const totalDots = Math.ceil(deals.length / 3); // 3 items per slide
			for (let i = 0; i < totalDots; i++) {
				const $dot = $(`<div class="bt-dot ${i === 0 ? 'active' : ''}" data-slide="${i}"></div>`);
				$dotsContainer.append($dot);
			}

			$container.append($carouselContainer);
			this.state.totalSlides = totalDots;
			this.updateCarouselLayout();
		},

		// Create Carousel Item
		createCarouselItem: function(deal, index) {
			const currency = btDealsData.currency || '₹';
			const imageUrl = deal.thumbnail || 'https://via.placeholder.com/280x160?text=No+Image';

			return $(`
				<div class="bt-carousel-item bt-fade-in" style="animation-delay: ${index * 0.1}s">
					<img src="${imageUrl}"
						 alt="${deal.title}"
						 class="bt-carousel-image"
						 loading="lazy"
						 width="280"
						 height="160">
					<h4 class="bt-carousel-title">${deal.title}</h4>
					<div class="bt-carousel-price">${currency}${deal.sale_price}</div>
					<div class="bt-carousel-store">${deal.store_name || 'Store'}</div>
					<a href="${deal.offer_url}"
					   class="bt-carousel-btn"
					   target="_blank"
					   rel="nofollow noopener"
					   onclick="btDeals.trackClick(${deal.id})">
						Get Deal
					</a>
				</div>
			`);
		},

		// Navigation Methods
		prevSlide: function() {
			if (this.state.currentSlide > 0) {
				this.goToSlide(this.state.currentSlide - 1);
			}
		},

		nextSlide: function() {
			if (this.state.currentSlide < this.state.totalSlides - 1) {
				this.goToSlide(this.state.currentSlide + 1);
			}
		},

		goToSlide: function(slideIndex) {
			this.state.currentSlide = slideIndex;
			this.updateCarouselPosition();
			this.updateNavigation();
			this.updateDots();
		},

		// Update Carousel Position
		updateCarouselPosition: function() {
			const $container = $('.bt-carousel-container');
			const translateX = -this.state.currentSlide * (this.config.carouselItemWidth * 3 + this.config.carouselGap * 2);
			$container.css('transform', `translateX(${translateX}px)`);
		},

		// Update Navigation Buttons
		updateNavigation: function() {
			const $prevBtn = $('#btPrevBtn');
			const $nextBtn = $('#btNextBtn');

			$prevBtn.prop('disabled', this.state.currentSlide === 0);
			$nextBtn.prop('disabled', this.state.currentSlide >= this.state.totalSlides - 1);
		},

		// Update Dots
		updateDots: function() {
			$('.bt-dot').removeClass('active');
			$(`.bt-dot[data-slide="${this.state.currentSlide}"]`).addClass('active');
		},

		// Update Carousel Layout
		updateCarouselLayout: function() {
			const containerWidth = $('.bt-similar-carousel').width();
			const itemWidth = Math.min(280, (containerWidth - 48) / 3); // 3 items per slide with gaps

			this.config.carouselItemWidth = itemWidth;
			$('.bt-carousel-item').css('flex', `0 0 ${itemWidth}px`);
			this.updateCarouselPosition();
		},

		// Touch/Swipe Support
		initTouchSupport: function() {
			let startX = 0;
			let currentX = 0;
			let isDragging = false;

			$('.bt-carousel-container').on('touchstart', (e) => {
				startX = e.touches[0].clientX;
				isDragging = true;
			});

			$('.bt-carousel-container').on('touchmove', (e) => {
				if (!isDragging) return;
				currentX = e.touches[0].clientX;
			});

			$('.bt-carousel-container').on('touchend', (e) => {
				if (!isDragging) return;

				const diff = startX - currentX;
				const threshold = 50;

				if (Math.abs(diff) > threshold) {
					if (diff > 0) {
						this.nextSlide();
					} else {
						this.prevSlide();
					}
				}

				isDragging = false;
			});
		},

		// Share Buttons
		initShareButtons: function() {
			const self = this;

			$(document).on('click', '.bt-share-btn', function(e) {
				e.preventDefault();
				const platform = $(this).data('platform');
				const url = btDealsData.shareUrl;
				const title = btDealsData.shareTitle;
				const text = btDealsData.shareText;

				self.shareOnPlatform(platform, url, title, text);
			});
		},

		// Share on Platform
		shareOnPlatform: function(platform, url, title, text) {
			const encodedUrl = encodeURIComponent(url);
			const encodedTitle = encodeURIComponent(title);
			const encodedText = encodeURIComponent(text);

			let shareUrl = '';

			switch (platform) {
				case 'facebook':
					shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`;
					break;
				case 'twitter':
					shareUrl = `https://twitter.com/intent/tweet?url=${encodedUrl}&text=${encodedTitle}`;
					break;
				case 'whatsapp':
					shareUrl = `https://wa.me/?text=${encodedText}%20${encodedUrl}`;
					break;
				case 'copy':
					this.copyToClipboard(url);
					this.showNotification('Link copied to clipboard!');
					return;
			}

			if (shareUrl) {
				window.open(shareUrl, '_blank', 'width=600,height=400');
			}
		},

		// Copy to Clipboard
		copyToClipboard: function(text) {
			if (navigator.clipboard && window.isSecureContext) {
				navigator.clipboard.writeText(text);
			} else {
				// Fallback for older browsers
				const textArea = document.createElement('textarea');
				textArea.value = text;
				document.body.appendChild(textArea);
				textArea.select();
				document.execCommand('copy');
				document.body.removeChild(textArea);
			}
		},

		// Coupon Reveal
		initCouponReveal: function() {
			$(document).on('click', '.bt-coupon-reveal', function(e) {
				e.preventDefault();
				const $button = $(this);
				const $code = $button.siblings('.bt-coupon-code');
				const couponCode = $button.data('coupon');

				if ($code.length) {
					$code.text(couponCode).show();
					$button.hide();
				}
			});
		},

		// Track Click
		trackClick: function(dealId) {
			$.ajax({
				url: btDealsData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'track_event',
					nonce: btDealsData.nonce,
					event_type: 'click',
					deal_id: dealId,
					extra_data: 'deal_page'
				}
			});
		},

		// Add Fade In Animations
		addFadeInAnimations: function() {
			const observerOptions = {
				threshold: 0.1,
				rootMargin: '0px 0px -50px 0px'
			};

			const observer = new IntersectionObserver((entries) => {
				entries.forEach(entry => {
					if (entry.isIntersecting) {
						entry.target.classList.add('bt-fade-in');
					}
				});
			}, observerOptions);

			// Observe elements
			document.querySelectorAll('.bt-hero-container, .bt-content-section, .bt-similar-deals-section').forEach(el => {
				observer.observe(el);
			});
		},

		// Show Notification
		showNotification: function(message, type = 'success') {
			const $notification = $(`
				<div class="bt-notification ${type === 'error' ? 'error' : ''}">
					${message}
				</div>
			`);

			$('body').append($notification);

			setTimeout(() => {
				$notification.fadeOut(() => {
					$notification.remove();
				});
			}, 3000);
		},

		// Show Error
		showError: function(message) {
			const $container = $('#btSimilarCarousel');
			$container.html(`
				<div class="bt-loading-similar">
					<p style="color: var(--bt-error);">${message}</p>
				</div>
			`);
		},

		// Utility: Debounce
		debounce: function(func, wait) {
			let timeout;
			return function executedFunction(...args) {
				const later = () => {
					clearTimeout(timeout);
					func(...args);
				};
				clearTimeout(timeout);
				timeout = setTimeout(later, wait);
			};
		},

		// Countdown Timer
		initCountdownTimer: function() {
			const $timer = $('.bt-countdown-timer');
			if ($timer.length === 0) return;

			const expiryDate = $timer.data('expiry');
			if (!expiryDate) return;

			this.updateCountdown(expiryDate);
			this.countdownInterval = setInterval(() => {
				this.updateCountdown(expiryDate);
			}, 1000);
		},

		// Update Countdown Display
		updateCountdown: function(expiryDate) {
			const now = new Date().getTime();
			const expiry = new Date(expiryDate).getTime();
			const distance = expiry - now;

			if (distance < 0) {
				// Deal has expired
				$('#btDays, #btHours, #btMinutes, #btSeconds').text('00');
				if (this.countdownInterval) {
					clearInterval(this.countdownInterval);
				}
				return;
			}

			const days = Math.floor(distance / (1000 * 60 * 60 * 24));
			const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
			const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
			const seconds = Math.floor((distance % (1000 * 60)) / 1000);

			$('#btDays').text(this.padNumber(days));
			$('#btHours').text(this.padNumber(hours));
			$('#btMinutes').text(this.padNumber(minutes));
			$('#btSeconds').text(this.padNumber(seconds));
		},

		// Pad Number with Leading Zero
		padNumber: function(num) {
			return num.toString().padStart(2, '0');
		},

		// Wishlist Functionality
		initWishlist: function() {
			const self = this;

			$(document).on('click', '.bt-wishlist-btn', function(e) {
				e.preventDefault();
				const $button = $(this);
				const dealId = $button.data('deal-id');

				if ($button.hasClass('bt-active')) {
					self.removeFromWishlist(dealId, $button);
				} else {
					self.addToWishlist(dealId, $button);
				}
			});

			// Check if deal is already in wishlist
			this.checkWishlistStatus();
		},

		// Add to Wishlist
		addToWishlist: function(dealId, $button) {
			const self = this;

			$.ajax({
				url: btDealsData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'add_to_wishlist',
					nonce: btDealsData.nonce,
					deal_id: dealId
				},
				success: function(response) {
					if (response.success) {
						$button.addClass('bt-active');
						$button.find('.bt-wishlist-text').text('Saved');
						self.showNotification('Added to wishlist!');
					} else {
						self.showNotification('Failed to add to wishlist', 'error');
					}
				},
				error: function() {
					self.showNotification('Failed to add to wishlist', 'error');
				}
			});
		},

		// Remove from Wishlist
		removeFromWishlist: function(dealId, $button) {
			const self = this;

			$.ajax({
				url: btDealsData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'remove_from_wishlist',
					nonce: btDealsData.nonce,
					deal_id: dealId
				},
				success: function(response) {
					if (response.success) {
						$button.removeClass('bt-active');
						$button.find('.bt-wishlist-text').text('Save Deal');
						self.showNotification('Removed from wishlist!');
					} else {
						self.showNotification('Failed to remove from wishlist', 'error');
					}
				},
				error: function() {
					self.showNotification('Failed to remove from wishlist', 'error');
				}
			});
		},

		// Check Wishlist Status
		checkWishlistStatus: function() {
			const dealId = btDealsData.postId;
			const self = this;

			$.ajax({
				url: btDealsData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'check_wishlist_status',
					nonce: btDealsData.nonce,
					deal_id: dealId
				},
				success: function(response) {
					if (response.success && response.in_wishlist) {
						$('.bt-wishlist-btn').addClass('bt-active');
						$('.bt-wishlist-text').text('Saved');
					}
				}
			});
		},

		// Share Trigger
		initShareTrigger: function() {
			const self = this;

			$(document).on('click', '.bt-share-trigger', function(e) {
				e.preventDefault();

				// Create share modal/popup
				const shareUrl = btDealsData.shareUrl;
				const shareTitle = btDealsData.shareTitle;
				const shareText = btDealsData.shareText;

				self.showShareModal(shareUrl, shareTitle, shareText);
			});
		},

		// Show Share Modal
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
								<button class="bt-share-btn-modal" data-platform="facebook">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
										<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
									</svg>
									<span>Facebook</span>
								</button>
								<button class="bt-share-btn-modal" data-platform="twitter">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
										<path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
									</svg>
									<span>Twitter</span>
								</button>
								<button class="bt-share-btn-modal" data-platform="whatsapp">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
										<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.89 3.685"/>
									</svg>
									<span>WhatsApp</span>
								</button>
								<button class="bt-share-btn-modal" data-platform="telegram">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
										<path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
									</svg>
									<span>Telegram</span>
								</button>
								<button class="bt-share-btn-modal" data-platform="linkedin">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
										<path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
									</svg>
									<span>LinkedIn</span>
								</button>
								<button class="bt-share-btn-modal" data-platform="copy">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
										<path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/>
									</svg>
									<span>Copy Link</span>
								</button>
							</div>
						</div>
					</div>
				</div>
			`;

			$('body').append(modalHtml);

			// Bind modal events
			$('.bt-share-modal-close, .bt-share-modal-overlay').on('click', function() {
				$('.bt-share-modal-overlay').remove();
			});

			// Bind share buttons in modal
			$('.bt-share-btn-modal').on('click', function() {
				const platform = $(this).data('platform');
				self.shareOnPlatform(platform, url, title, text);
				$('.bt-share-modal-overlay').remove();
			});
		},

		// Utility: Throttle
		throttle: function(func, limit) {
			let inThrottle;
			return function() {
				const args = arguments;
				const context = this;
				if (!inThrottle) {
					func.apply(context, args);
					inThrottle = true;
					setTimeout(() => inThrottle = false, limit);
				}
			};
		}
	};

	// Initialize on document ready
	$(document).ready(function() {
		btDeals.init();
	});

	// Performance: Preload images
	function preloadImages() {
		const images = document.querySelectorAll('img[loading="lazy"]');
		images.forEach(img => {
			const link = document.createElement('link');
			link.rel = 'preload';
			link.as = 'image';
			link.href = img.src;
			document.head.appendChild(link);
		});
	}

	// Run preload after page load
	$(window).on('load', function() {
		setTimeout(preloadImages, 100);
	});

})(jQuery);

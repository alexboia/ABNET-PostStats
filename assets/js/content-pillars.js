/**
 * Content Pillars Admin Page JavaScript
 * Handles category search, selection, and multi-select functionality
 * 
 * @package ABNet_Post_Stats
 * @since 1.0.0
 */

(function($) {
	'use strict';
	
	// Global variables that will be set by WordPress localization
	var categories = window.abnetContentPillars?.categories || [];
	var mostUsedCategories = window.abnetContentPillars?.mostUsedCategories || [];
	var selectedCategoryIds = new Set();
	
	// DOM elements
	var categorySearch, categoryDropdown, selectedCategories;
	
	/**
	 * Initialize the content pillars functionality
	 */
	function init() {
		// Get DOM elements
		categorySearch = document.getElementById('category_search');
		categoryDropdown = document.getElementById('category-dropdown');
		selectedCategories = document.getElementById('selected-categories');
		
		if (!categorySearch || !categoryDropdown || !selectedCategories) {
			return; // Elements not found, exit
		}
		
		// Initialize selected categories
		initializeSelectedCategories();
		
		// Bind events
		bindEvents();
	}
	
	/**
	 * Initialize already selected categories
	 */
	function initializeSelectedCategories() {
		document.querySelectorAll('.selected-category').forEach(function(element) {
			selectedCategoryIds.add(parseInt(element.dataset.categoryId));
		});
	}
	
	/**
	 * Bind all event listeners
	 */
	function bindEvents() {
		// Show most used categories on focus (when empty)
		categorySearch.addEventListener('focus', function() {
			if (this.value.trim() === '') {
				showMostUsedCategories();
			}
		});
		
		// Search functionality
		categorySearch.addEventListener('input', function() {
			handleSearchInput(this.value);
		});
		
		// Category selection
		categoryDropdown.addEventListener('click', function(e) {
			handleCategorySelection(e);
		});
		
		// Remove category
		selectedCategories.addEventListener('click', function(e) {
			handleCategoryRemoval(e);
		});
		
		// Hide dropdown when clicking outside
		document.addEventListener('click', function(e) {
			if (!e.target.closest('#abnet-category-selector')) {
				categoryDropdown.style.display = 'none';
			}
		});
		
		// Clear all categories button
		var clearAllButton = document.getElementById('clear-all-categories');
		if (clearAllButton) {
			clearAllButton.addEventListener('click', function() {
				clearAllCategories();
			});
		}
	}
	
	/**
	 * Handle search input
	 * @param {string} value The search input value
	 */
	function handleSearchInput(value) {
		var searchTerm = removeDiacritics(value.toLowerCase());
		
		if (searchTerm.length === 0) {
			showMostUsedCategories();
			return;
		} else if (searchTerm.length < 2) {
			categoryDropdown.style.display = 'none';
			return;
		}
		
		var filteredCategories = categories.filter(function(category) {
			var categoryName = removeDiacritics(category.name.toLowerCase());
			return categoryName.includes(searchTerm) && 
				   !selectedCategoryIds.has(category.id);
		});
		
		showCategoryDropdown(filteredCategories);
	}
	
	/**
	 * Handle category selection from dropdown
	 * @param {Event} e Click event
	 */
	function handleCategorySelection(e) {
		if (e.target.classList.contains('category-option')) {
			var categoryId = parseInt(e.target.dataset.categoryId);
			var categoryName = e.target.dataset.categoryName || 
							   e.target.textContent.replace(/\s*\(\d+\)\s*$/, '').trim();
			
			addSelectedCategory(categoryId, categoryName);
			categorySearch.value = '';
			categoryDropdown.style.display = 'none';
		}
	}
	
	/**
	 * Handle category removal
	 * @param {Event} e Click event
	 */
	function handleCategoryRemoval(e) {
		if (e.target.classList.contains('remove-category')) {
			var categorySpan = e.target.parentElement;
			var categoryId = parseInt(categorySpan.dataset.categoryId);
			
			selectedCategoryIds.delete(categoryId);
			categorySpan.remove();
		}
	}
	
	/**
	 * Show most used categories in dropdown
	 */
	function showMostUsedCategories() {
		var availableMostUsed = mostUsedCategories.filter(function(category) {
			return !selectedCategoryIds.has(category.id);
		});
		
		if (availableMostUsed.length > 0) {
			categoryDropdown.innerHTML = '<div class="category-section-header">Most Used Categories</div>' +
				availableMostUsed.map(function(category) {
					return '<div class="category-option" data-category-id="' + category.id + 
						   '" data-category-name="' + escapeHtml(category.name) + '">' + 
						   escapeHtml(category.name) + ' <span class="category-count">(' + 
						   category.count + ')</span></div>';
				}).join('');
			categoryDropdown.style.display = 'block';
		} else {
			categoryDropdown.style.display = 'none';
		}
	}
	
	/**
	 * Show filtered categories in dropdown
	 * @param {Array} filteredCategories Array of categories to show
	 */
	function showCategoryDropdown(filteredCategories) {
		if (filteredCategories.length > 0) {
			categoryDropdown.innerHTML = filteredCategories.map(function(category) {
				return '<div class="category-option" data-category-id="' + category.id + 
					   '" data-category-name="' + escapeHtml(category.name) + '">' + 
					   escapeHtml(category.name) + '</div>';
			}).join('');
			categoryDropdown.style.display = 'block';
		} else {
			categoryDropdown.style.display = 'none';
		}
	}
	
	/**
	 * Add a selected category to the UI
	 * @param {number} categoryId Category ID
	 * @param {string} categoryName Category name
	 */
	function addSelectedCategory(categoryId, categoryName) {
		selectedCategoryIds.add(categoryId);
		
		var categorySpan = document.createElement('span');
		categorySpan.className = 'selected-category';
		categorySpan.dataset.categoryId = categoryId;
		categorySpan.innerHTML = escapeHtml(categoryName) + 
			'<span class="remove-category">&times;</span>' +
			'<input type="hidden" name="category_ids[]" value="' + categoryId + '">';
		
		selectedCategories.appendChild(categorySpan);
	}
	
	/**
	 * Remove diacritics from string for search
	 * @param {string} str String to process
	 * @return {string} String without diacritics
	 */
	function removeDiacritics(str) {
		return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
	}
	
	/**
	 * Clear all selected categories
	 */
	function clearAllCategories() {
		// Get confirmation message from localized strings
		var confirmMessage = window.abnetContentPillars?.strings?.confirmClearAll || 'Are you sure you want to remove all selected categories?';
		
		// Confirm action
		if (!confirm(confirmMessage)) {
			return;
		}
		
		// Clear the Set
		selectedCategoryIds.clear();
		
		// Remove all category elements from DOM
		var categoryElements = selectedCategories.querySelectorAll('.selected-category');
		categoryElements.forEach(function(element) {
			element.remove();
		});
		
		// Clear search input
		categorySearch.value = '';
		
		// Hide dropdown
		categoryDropdown.style.display = 'none';
	}
	
	/**
	 * Escape HTML to prevent XSS
	 * @param {string} text Text to escape
	 * @return {string} Escaped HTML
	 */
	function escapeHtml(text) {
		var div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}
	
	// Initialize when DOM is ready
	document.addEventListener('DOMContentLoaded', init);
	
})(jQuery);

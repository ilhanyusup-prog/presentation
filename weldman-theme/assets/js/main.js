/**
 * Weldman theme — small vanilla-JS enhancements.
 * No dependencies, no build step required.
 */
( function () {
	'use strict';

	var toggle = document.getElementById( 'menu-toggle' );
	var nav = document.getElementById( 'site-navigation' );

	if ( toggle && nav ) {
		toggle.addEventListener( 'click', function () {
			var isOpen = nav.classList.toggle( 'is-open' );
			toggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
			document.body.classList.toggle( 'menu-open', isOpen );
		} );

		// Close the mobile menu when a link is clicked (except submenu parents).
		nav.addEventListener( 'click', function ( event ) {
			var link = event.target.closest( 'a' );
			if ( ! link ) {
				return;
			}
			var parentItem = link.closest( '.menu-item-has-children' );
			if ( parentItem && window.innerWidth < 768 ) {
				// First tap on a parent link with children toggles the submenu
				// instead of navigating away, so touch users can reach sub-items.
				var hasOpened = parentItem.classList.contains( 'is-open' );
				if ( ! hasOpened ) {
					event.preventDefault();
					parentItem.classList.add( 'is-open' );
					return;
				}
			}
			if ( window.innerWidth < 768 ) {
				nav.classList.remove( 'is-open' );
				toggle.setAttribute( 'aria-expanded', 'false' );
				document.body.classList.remove( 'menu-open' );
			}
		} );

		// Reset menu state when resizing across the desktop breakpoint.
		window.addEventListener( 'resize', function () {
			if ( window.innerWidth >= 768 ) {
				nav.classList.remove( 'is-open' );
				toggle.setAttribute( 'aria-expanded', 'false' );
				document.body.classList.remove( 'menu-open' );
			}
		} );
	}
} )();

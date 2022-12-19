/* global assistantLocalizeObj */
( function() {
	var closeLink = document.querySelector( '.welcome-panel a.welcome-panel-close' );
	if ( ! closeLink ) {
		return;
	}

	if ( ! closeLink.getAttribute( 'aria-label' ) ) {
		return;
	}

	closeLink.setAttribute( 'aria-label', assistantLocalizeObj.closeLinkLabel );
} () );
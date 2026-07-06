/**
 * Altersverifikation – Admin JS (minimal).
 */
( function () {
	'use strict';

	var scopeSelect = document.getElementById( 'kipphard-age-verification-scope' );
	var pagesRow    = document.getElementById( 'kipphard-age-verification-pages-row' );

	function togglePagesRow() {
		if ( ! scopeSelect || ! pagesRow ) {
			return;
		}
		pagesRow.style.display = scopeSelect.value === 'pages' ? '' : 'none';
	}

	if ( scopeSelect ) {
		scopeSelect.addEventListener( 'change', togglePagesRow );
		togglePagesRow();
	}
}() );

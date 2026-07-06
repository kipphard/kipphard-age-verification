/* global kipphardAgeVerificationData */
/**
 * Altersverifikation – Gate-Logik (vanilla IIFE, kein Framework, kein Tracking).
 * Geburtsdatum wird ausschließlich client-seitig ausgewertet; keine Daten an den Server.
 */
( function () {
	'use strict';

	var cfg = window.kipphardAgeVerificationData || {};
	var cookieName    = cfg.cookieName    || 'kipphard_age_verification_ok';
	var mode          = cfg.mode          || 'confirm';
	var minAge        = parseInt( cfg.minAge, 10 ) || 18;
	var rememberDays  = parseInt( cfg.rememberDays, 10 ) || 30;
	var declineAction = cfg.declineAction || 'message';
	var declineUrl    = cfg.declineUrl    || '';

	var overlay      = document.getElementById( 'kipphard-age-verification-overlay' );
	var btnConfirm   = document.getElementById( 'kipphard-age-verification-confirm' );
	var btnDecline   = document.getElementById( 'kipphard-age-verification-decline' );
	var declineMsg   = document.getElementById( 'kipphard-age-verification-decline-message' );
	var dobError     = document.getElementById( 'kipphard-age-verification-dob-error' );
	var fieldDay     = document.getElementById( 'kipphard-age-verification-day' );
	var fieldMonth   = document.getElementById( 'kipphard-age-verification-month' );
	var fieldYear    = document.getElementById( 'kipphard-age-verification-year' );

	if ( ! overlay ) {
		return;
	}

	/**
	 * Cookie nach Name auslesen.
	 *
	 * @param {string} name Cookie-Name.
	 * @returns {string|null}
	 */
	function getCookie( name ) {
		var match = document.cookie.match( new RegExp( '(?:^|; )' + name.replace( /([.*+?^=!:${}()|[\]/\\])/g, '\\$1' ) + '=([^;]*)' ) );
		return match ? decodeURIComponent( match[1] ) : null;
	}

	/**
	 * Funktionales Cookie setzen (kein PII – nur Bestätigungsstatus).
	 */
	function setConfirmCookie() {
		var maxAge = rememberDays * 86400;
		document.cookie = cookieName + '=1; max-age=' + maxAge + '; path=/; SameSite=Lax';
	}

	/**
	 * Overlay entfernen und Seite zugänglich machen.
	 */
	function removeOverlay() {
		overlay.classList.add( 'kipphard-age-verification-hidden' );
		overlay.setAttribute( 'aria-hidden', 'true' );
	}

	/**
	 * Altersberechnung aus Tag, Monat, Jahr.
	 *
	 * @param {number} day   Tag (1–31).
	 * @param {number} month Monat (1–12).
	 * @param {number} year  Jahr (4-stellig).
	 * @returns {number} Alter in vollen Jahren.
	 */
	function calculateAge( day, month, year ) {
		var now      = new Date();
		var birthDate = new Date( year, month - 1, day );
		var age      = now.getFullYear() - birthDate.getFullYear();
		var mDiff    = now.getMonth() - birthDate.getMonth();
		if ( mDiff < 0 || ( mDiff === 0 && now.getDate() < birthDate.getDate() ) ) {
			age--;
		}
		return age;
	}

	/**
	 * Akzeptieren: Cookie setzen + Overlay ausblenden.
	 */
	function accept() {
		setConfirmCookie();
		removeOverlay();
	}

	/**
	 * Ablehnen: Meldung anzeigen oder weiterleiten.
	 */
	function decline() {
		if ( declineAction === 'redirect' && declineUrl ) {
			window.location.assign( declineUrl );
		} else {
			if ( declineMsg ) {
				declineMsg.style.display = 'block';
			}
			// Overlay bleibt sichtbar – Inhalt bleibt gesperrt.
		}
	}

	/**
	 * Bestätigungsfluss je nach Modus.
	 */
	function handleConfirm() {
		if ( mode === 'dob' ) {
			var day   = parseInt( fieldDay   ? fieldDay.value   : 0, 10 );
			var month = parseInt( fieldMonth ? fieldMonth.value : 0, 10 );
			var year  = parseInt( fieldYear  ? fieldYear.value  : 0, 10 );

			if ( ! day || ! month || ! year || year < 1900 || year > new Date().getFullYear() ) {
				if ( dobError ) {
					dobError.style.display = 'block';
				}
				return;
			}

			var age = calculateAge( day, month, year );
			if ( age >= minAge ) {
				if ( dobError ) {
					dobError.style.display = 'none';
				}
				accept();
			} else {
				if ( dobError ) {
					dobError.style.display = 'block';
				}
				decline();
			}
		} else {
			// Modus: confirm – einfache Bestätigung.
			accept();
		}
	}

	// -------------------------------------------------------------------------
	// Initialisierung
	// -------------------------------------------------------------------------

	// Wenn gültiges Remember-Cookie vorhanden → Overlay sofort entfernen.
	if ( getCookie( cookieName ) === '1' ) {
		removeOverlay();
		return;
	}

	// Event-Listener.
	if ( btnConfirm ) {
		btnConfirm.addEventListener( 'click', handleConfirm );
	}

	if ( btnDecline ) {
		btnDecline.addEventListener( 'click', decline );
	}

	// Fokus-Falle: Tab bleibt im Overlay.
	overlay.addEventListener( 'keydown', function ( e ) {
		if ( e.key !== 'Tab' ) {
			return;
		}
		var focusable = overlay.querySelectorAll(
			'button, input, a[href], select, textarea, [tabindex]:not([tabindex="-1"])'
		);
		if ( ! focusable.length ) {
			return;
		}
		var first = focusable[0];
		var last  = focusable[ focusable.length - 1 ];
		if ( e.shiftKey ) {
			if ( document.activeElement === first ) {
				e.preventDefault();
				last.focus();
			}
		} else {
			if ( document.activeElement === last ) {
				e.preventDefault();
				first.focus();
			}
		}
	} );

	// Initialen Fokus auf den Bestätigen-Button setzen.
	if ( btnConfirm ) {
		btnConfirm.focus();
	}
}() );

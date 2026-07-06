<?php
/**
 * Plugin-Deinstallation: alle Plugin-Optionen aus der Datenbank entfernen.
 *
 * @package Kipphard\Altersverifikation
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'kipphard_age_verification_settings' );

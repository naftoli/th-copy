/**
 * Component constants file
 * 
 * This file exports all constants that are shared by multiple components.
 * These constants are generally related to glueing together the legacy system to this one.
 */

// url to use when navigating to older pages
export const LEGACY_URL = process.env.NODE_ENV === "production" ? "" : "//mashpia.local";

// fallback images
export const DEFAULT_PROFILE = '/mobile/reg/images/profile-photo-default.jpg';
export const DEFAULT_LOGO = '/schoolLogos/logo.png';
export const DEFAULT_PRIZE = '/v2/images/imgsrepo/default.jpg';

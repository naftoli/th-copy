/**
 * Component constants file
 * 
 * This file exports all constants that are shared by multiple components.
 * These constants are generally related to glueing together the legacy system to this one.
 */

// url to use when navigating to older pages
export const LEGACY_URL = import.meta.env.MODE === "production" ? "" : "//localhost";

// fallback images
export const DEFAULT_PROFILE = '/mobile/reg/images/profile-photo-default.jpg';
export const DEFAULT_LOGO = '/schoolLogos/logo.png';
export const DEFAULT_PRIZE = '/v2/images/imgsrepo/default.png';

// api keys
export const GOOGLE_CLIENT_ID = '356394568289-o9uqieb96qevc8a1plmm5voa6so0l2fd.apps.googleusercontent.com';
export const MAPBOX_ACCESS_TOKEN = 'pk.eyJ1IjoibmFmdG9saXIiLCJhIjoiY2xsZTR5NHFuMDBzeTNzdWxqcnd1ZGJ0ZSJ9.7M6yPlYYDHRE-QcRUp_z9A';
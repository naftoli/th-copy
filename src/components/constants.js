/**
 * Component constants file
 * 
 * This file exports all constants that are shared by multiple components.
 * These constants are generally related to glueing together the legacy system to this one.
 */

// url to use when navigating to older pages
export const LEGACY_URL = process.env.NODE_ENV === "production" ? "" : "//10.0.2.15";
export const DEFAULT_PROFILE = '/mobile/reg/images/profile-photo-default.jpg';
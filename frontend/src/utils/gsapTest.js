/**
 * GSAP Installation Test
 * This file verifies that GSAP is correctly installed and can be imported
 */

import gsap from 'gsap';

// Simple test to verify GSAP is available
export function testGsapInstallation() {
  try {
    // Check if gsap object exists
    if (!gsap) {
      throw new Error('GSAP is not available');
    }

    // Check if core GSAP methods exist
    if (typeof gsap.to !== 'function' || 
        typeof gsap.from !== 'function' || 
        typeof gsap.fromTo !== 'function') {
      throw new Error('GSAP core methods are not available');
    }

    console.log('✓ GSAP is correctly installed and configured');
    console.log('✓ GSAP version:', gsap.version);
    return true;
  } catch (error) {
    console.error('✗ GSAP installation test failed:', error.message);
    return false;
  }
}

// Export GSAP for use in components
export { gsap };

# GSAP Installation Documentation

## Installation Summary

GSAP (GreenSock Animation Platform) has been successfully installed and configured in the frontend application for the maintenance calendar bento feature.

### Package Information

- **Package**: gsap
- **Version**: ^3.15.0
- **Installation Date**: 2024
- **Location**: `frontend/package.json` dependencies

### Installation Command

```bash
npm install gsap
```

## Configuration

### Vite Configuration

GSAP works out-of-the-box with the current Vite configuration. No additional configuration is required as:

1. GSAP is a standard ES module
2. Vite handles ES module imports natively
3. The current `vite.config.js` is already optimized for ES modules

### Current vite.config.js

```javascript
import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
    extensions: ['.js', '.vue', '.json']
  },
})
```

**No changes needed** - This configuration is compatible with GSAP.

## Usage in Components

### Basic Import

```javascript
import gsap from 'gsap';
```

### Usage in Vue 3 Composition API

```vue
<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import gsap from 'gsap';

const elementRef = ref(null);

onMounted(() => {
  // Animate element on mount
  gsap.from(elementRef.value, {
    opacity: 0,
    y: 20,
    duration: 0.5,
    ease: 'power2.out'
  });
});

onUnmounted(() => {
  // Clean up animations on unmount
  gsap.killTweensOf(elementRef.value);
});
</script>

<template>
  <div ref="elementRef">
    Animated content
  </div>
</template>
```

### Planned Usage for Maintenance Calendar

GSAP will be used for:

1. **Calendar Grid Animation** (Requirement 11.1)
   - Fade and translate animation on mount
   - Staggered animation for calendar cells
   - Duration: <800ms

2. **Modal Details Animation** (Requirement 11.2)
   - Scale and opacity animation on open
   - Reverse animation on close
   - Smooth easing for natural movement

3. **Event Cards Animation**
   - Scale animation on appearance
   - Staggered animation for multiple events

## Verification

### Build Verification

The build has been successfully completed with GSAP:

```bash
npm run build-only
```

✓ 315 modules transformed
✓ Built in ~6.3s
✓ No GSAP-related errors

### Test File

A test utility has been created at `frontend/src/utils/gsapTest.js` to verify GSAP installation.

## Performance Considerations

- **Bundle Size**: GSAP core is ~51KB (gzipped)
- **Tree Shaking**: Vite automatically tree-shakes unused GSAP features
- **Optimization**: Only import what you need (e.g., `gsap.to`, `gsap.from`)

## Requirements Validation

### Requirement 11.1: GSAP in Dependencies ✓

- [x] GSAP added to `package.json`
- [x] Version: ^3.15.0
- [x] Installed in dependencies (not devDependencies)

### Requirement 11.2: Vite Configuration ✓

- [x] Vite configuration checked
- [x] No additional configuration needed
- [x] ES module support confirmed
- [x] Build successful with GSAP

## Next Steps

1. Create `frontend/src/utils/gsapAnimations.js` utility file
2. Implement calendar grid animations in `MaintenanceCalendarView.vue`
3. Implement modal animations in `MaintenanceDetailsModal.vue`
4. Test animations in development mode
5. Verify animations in production build

## References

- [GSAP Documentation](https://gsap.com/docs/v3/)
- [GSAP with Vue 3](https://gsap.com/resources/Vue/)
- [Vite ES Module Support](https://vitejs.dev/guide/features.html#npm-dependency-resolving-and-pre-bundling)

## Troubleshooting

### If GSAP doesn't import

```bash
# Reinstall dependencies
cd frontend
npm install
```

### If animations don't work

1. Check browser console for errors
2. Verify element refs are defined
3. Ensure animations run after DOM is mounted (`onMounted`)
4. Clean up animations in `onUnmounted` to prevent memory leaks

## Support

For issues specific to this installation, refer to:
- Design document: `.kiro/specs/maintenance-calendar-bento/design.md`
- Requirements: `.kiro/specs/maintenance-calendar-bento/requirements.md`
- Tasks: `.kiro/specs/maintenance-calendar-bento/tasks.md`

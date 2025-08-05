#!/usr/bin/env node

/**
 * Generate CSS classes JSON file for Symfony backend
 * This script exports ALL_CSS_CLASSES from the frontend safelist to a JSON file
 * that can be consumed by the PHP backend for select-css field types.
 * 
 * Usage:
 * node scripts/generate-css-classes.js
 * 
 * Or add to your package.json scripts:
 * "build:css-classes": "node scripts/generate-css-classes.js"
 */

const fs = require('fs');
const path = require('path');

// Essential Tailwind CSS classes that mimic Bootstrap 5 functionality
// Focused on core utilities without overwhelming users
const ESSENTIAL_CLASSES = [
  // === LAYOUT & GRID (Bootstrap container, row, col equivalents) ===
  'container',
  
  // Display utilities (Bootstrap d-* classes)
  'block', 'inline-block', 'inline', 'flex', 'inline-flex', 'grid', 'hidden',
  
  // Flexbox utilities (Bootstrap d-flex, justify-content-*, align-items-*)
  'flex-row', 'flex-col', 'flex-wrap', 'flex-nowrap',
  'justify-start', 'justify-center', 'justify-end', 'justify-between', 'justify-around',
  'items-start', 'items-center', 'items-end', 'items-stretch',
  
  // Grid system (Bootstrap col-* equivalents)
  'grid-cols-1', 'grid-cols-2', 'grid-cols-3', 'grid-cols-4', 'grid-cols-6', 'grid-cols-12',
  'col-span-1', 'col-span-2', 'col-span-3', 'col-span-4', 'col-span-6', 'col-span-12',
  'gap-2', 'gap-4', 'gap-6', 'gap-8',
  
  // === SPACING (Bootstrap m-*, p-* classes) ===
  // Margin utilities
  'm-0', 'm-1', 'm-2', 'm-3', 'm-4', 'm-5', 'm-6', 'm-8', 'm-auto',
  'mx-0', 'mx-1', 'mx-2', 'mx-3', 'mx-4', 'mx-5', 'mx-6', 'mx-8', 'mx-auto',
  'my-0', 'my-1', 'my-2', 'my-3', 'my-4', 'my-5', 'my-6', 'my-8',
  'mt-0', 'mt-1', 'mt-2', 'mt-3', 'mt-4', 'mt-5', 'mt-6', 'mt-8',
  'mb-0', 'mb-1', 'mb-2', 'mb-3', 'mb-4', 'mb-5', 'mb-6', 'mb-8',
  'ml-0', 'ml-1', 'ml-2', 'ml-3', 'ml-4', 'ml-5', 'ml-6', 'ml-8', 'ml-auto',
  'mr-0', 'mr-1', 'mr-2', 'mr-3', 'mr-4', 'mr-5', 'mr-6', 'mr-8', 'mr-auto',
  
  // Padding utilities
  'p-0', 'p-1', 'p-2', 'p-3', 'p-4', 'p-5', 'p-6', 'p-8',
  'px-0', 'px-1', 'px-2', 'px-3', 'px-4', 'px-5', 'px-6', 'px-8',
  'py-0', 'py-1', 'py-2', 'py-3', 'py-4', 'py-5', 'py-6', 'py-8',
  'pt-0', 'pt-1', 'pt-2', 'pt-3', 'pt-4', 'pt-5', 'pt-6', 'pt-8',
  'pb-0', 'pb-1', 'pb-2', 'pb-3', 'pb-4', 'pb-5', 'pb-6', 'pb-8',
  'pl-0', 'pl-1', 'pl-2', 'pl-3', 'pl-4', 'pl-5', 'pl-6', 'pl-8',
  'pr-0', 'pr-1', 'pr-2', 'pr-3', 'pr-4', 'pr-5', 'pr-6', 'pr-8',
  
  // === SIZING (Bootstrap w-*, h-* classes) ===
  'w-auto', 'w-full', 'w-1/2', 'w-1/3', 'w-2/3', 'w-1/4', 'w-3/4',
  'h-auto', 'h-full', 'h-screen', 'h-32', 'h-48', 'h-64',
  'max-w-xs', 'max-w-sm', 'max-w-md', 'max-w-lg', 'max-w-xl', 'max-w-2xl', 'max-w-full',
  
  // === COLORS (Bootstrap text-*, bg-* classes) ===
  // Background colors (Bootstrap contextual colors)
  'bg-white', 'bg-gray-100', 'bg-gray-200', 'bg-gray-300', 'bg-gray-800', 'bg-gray-900',
  'bg-blue-500', 'bg-blue-600', 'bg-blue-700',
  'bg-green-500', 'bg-green-600', 'bg-green-700',
  'bg-red-500', 'bg-red-600', 'bg-red-700',
  'bg-yellow-400', 'bg-yellow-500', 'bg-yellow-600',
  'bg-indigo-500', 'bg-indigo-600', 'bg-indigo-700',
  
  // Text colors
  'text-white', 'text-black', 'text-gray-500', 'text-gray-600', 'text-gray-700', 'text-gray-800', 'text-gray-900',
  'text-blue-500', 'text-blue-600', 'text-blue-700',
  'text-green-500', 'text-green-600', 'text-green-700',
  'text-red-500', 'text-red-600', 'text-red-700',
  'text-yellow-600', 'text-yellow-700',
  'text-indigo-500', 'text-indigo-600', 'text-indigo-700',
  
  // === TYPOGRAPHY (Bootstrap text-*, font-* classes) ===
  'text-xs', 'text-sm', 'text-base', 'text-lg', 'text-xl', 'text-2xl', 'text-3xl',
  'font-light', 'font-normal', 'font-medium', 'font-semibold', 'font-bold',
  'text-left', 'text-center', 'text-right',
  'uppercase', 'lowercase', 'capitalize',
  'underline', 'no-underline',
  
  // === BORDERS (Bootstrap border-* classes) ===
  'border', 'border-0', 'border-2', 'border-4',
  'border-gray-200', 'border-gray-300', 'border-gray-400',
  'border-blue-500', 'border-red-500', 'border-green-500',
  'rounded', 'rounded-none', 'rounded-sm', 'rounded-md', 'rounded-lg', 'rounded-full',
  'rounded-t', 'rounded-r', 'rounded-b', 'rounded-l',
  
  // === EFFECTS (Bootstrap shadow-* classes) ===
  'shadow-none', 'shadow-sm', 'shadow', 'shadow-md', 'shadow-lg',
  'opacity-25', 'opacity-50', 'opacity-75', 'opacity-100',
  
  // === POSITION (Bootstrap position-* classes) ===
  'static', 'relative', 'absolute', 'fixed', 'sticky',
  'top-0', 'right-0', 'bottom-0', 'left-0',
  'z-10', 'z-20', 'z-30', 'z-40', 'z-50',
  
  // === RESPONSIVE UTILITIES ===
  // Most common responsive variants for key utilities
  'sm:block', 'sm:hidden', 'sm:flex',
  'md:block', 'md:hidden', 'md:flex',
  'lg:block', 'lg:hidden', 'lg:flex',
  'sm:w-full', 'md:w-1/2', 'lg:w-1/3',
  'sm:text-sm', 'md:text-base', 'lg:text-lg',
  
  // === INTERACTIVE STATES ===
  // Hover states for common utilities
  'hover:bg-gray-100', 'hover:bg-blue-600', 'hover:bg-green-600', 'hover:bg-red-600',
  'hover:text-blue-600', 'hover:text-green-600', 'hover:text-red-600',
  'hover:shadow-md', 'hover:shadow-lg',
  
  // Focus states
  'focus:outline-none', 'focus:ring-2', 'focus:ring-blue-500', 'focus:ring-offset-2',
  
  // === FORM UTILITIES (Bootstrap form-* classes) ===
  'appearance-none', 'outline-none',
  'resize-none', 'resize-y', 'resize-x',
  
  // === OVERFLOW ===
  'overflow-hidden', 'overflow-auto', 'overflow-scroll',
  'overflow-x-hidden', 'overflow-y-hidden',
  'overflow-x-auto', 'overflow-y-auto'
];

// Use the predefined essential classes (no pattern expansion needed)
const ALL_CSS_CLASSES = ESSENTIAL_CLASSES;

// Enhanced class description generator with Bootstrap equivalents
function describeClass(cls) {
  const breakpoints = ['sm', 'md', 'lg', 'xl', '2xl'];
  const states = ['hover', 'focus', 'active', 'group-hover', 'dark', 'dark:hover', 'dark:focus'];

  const [maybeVariant, ...rest] = cls.split(':');
  const variant = breakpoints.includes(maybeVariant) || states.includes(maybeVariant) ? maybeVariant : '';
  const base = variant ? rest.join(':') : cls;

  let description = '';
  let bootstrapEquivalent = '';

  // Layout & Display utilities
  if (base === 'container') {
    description = 'Container with responsive max-widths';
    bootstrapEquivalent = '(Bootstrap: .container)';
  } else if (base === 'mx-auto') {
    description = 'Center horizontally with auto margins';
    bootstrapEquivalent = '(Bootstrap: .mx-auto)';
  } else if (base === 'block') {
    description = 'Display as block element';
    bootstrapEquivalent = '(Bootstrap: .d-block)';
  } else if (base === 'inline-block') {
    description = 'Display as inline-block element';
    bootstrapEquivalent = '(Bootstrap: .d-inline-block)';
  } else if (base === 'inline') {
    description = 'Display as inline element';
    bootstrapEquivalent = '(Bootstrap: .d-inline)';
  } else if (base === 'flex') {
    description = 'Display as flexbox container';
    bootstrapEquivalent = '(Bootstrap: .d-flex)';
  } else if (base === 'inline-flex') {
    description = 'Display as inline flexbox container';
    bootstrapEquivalent = '(Bootstrap: .d-inline-flex)';
  } else if (base === 'grid') {
    description = 'Display as CSS grid container';
    bootstrapEquivalent = '(Bootstrap: .d-grid)';
  } else if (base === 'hidden') {
    description = 'Hide element';
    bootstrapEquivalent = '(Bootstrap: .d-none)';
  }
  
  // Flexbox utilities
  else if (base === 'flex-row') {
    description = 'Flex direction row';
    bootstrapEquivalent = '(Bootstrap: .flex-row)';
  } else if (base === 'flex-col') {
    description = 'Flex direction column';
    bootstrapEquivalent = '(Bootstrap: .flex-column)';
  } else if (base === 'justify-center') {
    description = 'Center items horizontally';
    bootstrapEquivalent = '(Bootstrap: .justify-content-center)';
  } else if (base === 'justify-between') {
    description = 'Space items evenly with space between';
    bootstrapEquivalent = '(Bootstrap: .justify-content-between)';
  } else if (base === 'justify-around') {
    description = 'Space items evenly with space around';
    bootstrapEquivalent = '(Bootstrap: .justify-content-around)';
  } else if (base === 'items-center') {
    description = 'Center items vertically';
    bootstrapEquivalent = '(Bootstrap: .align-items-center)';
  } else if (base === 'items-start') {
    description = 'Align items to start';
    bootstrapEquivalent = '(Bootstrap: .align-items-start)';
  } else if (base === 'items-end') {
    description = 'Align items to end';
    bootstrapEquivalent = '(Bootstrap: .align-items-end)';
  }
  
  // Grid utilities
  else if (base.startsWith('grid-cols-')) {
    const cols = base.replace('grid-cols-', '');
    description = `Grid with ${cols} columns`;
    bootstrapEquivalent = '(Bootstrap: grid system)';
  } else if (base.startsWith('col-span-')) {
    const span = base.replace('col-span-', '');
    description = `Span ${span} grid columns`;
    bootstrapEquivalent = `(Bootstrap: .col-${span})`;
  }
  
  // Spacing utilities with Bootstrap equivalents
  else if (base.match(/^m-/)) {
    const spacing = base.replace('m-', '');
    description = `Margin: ${spacing}`;
    bootstrapEquivalent = `(Bootstrap: .m-${spacing})`;
  } else if (base.match(/^mx-/)) {
    const spacing = base.replace('mx-', '');
    description = `Horizontal margin: ${spacing}`;
    bootstrapEquivalent = `(Bootstrap: .mx-${spacing})`;
  } else if (base.match(/^my-/)) {
    const spacing = base.replace('my-', '');
    description = `Vertical margin: ${spacing}`;
    bootstrapEquivalent = `(Bootstrap: .my-${spacing})`;
  } else if (base.match(/^mt-/)) {
    const spacing = base.replace('mt-', '');
    description = `Top margin: ${spacing}`;
    bootstrapEquivalent = `(Bootstrap: .mt-${spacing})`;
  } else if (base.match(/^mb-/)) {
    const spacing = base.replace('mb-', '');
    description = `Bottom margin: ${spacing}`;
    bootstrapEquivalent = `(Bootstrap: .mb-${spacing})`;
  } else if (base.match(/^ml-/)) {
    const spacing = base.replace('ml-', '');
    description = `Left margin: ${spacing}`;
    bootstrapEquivalent = `(Bootstrap: .ml-${spacing} or .ms-${spacing})`;
  } else if (base.match(/^mr-/)) {
    const spacing = base.replace('mr-', '');
    description = `Right margin: ${spacing}`;
    bootstrapEquivalent = `(Bootstrap: .mr-${spacing} or .me-${spacing})`;
  } else if (base.match(/^p-/)) {
    const spacing = base.replace('p-', '');
    description = `Padding: ${spacing}`;
    bootstrapEquivalent = `(Bootstrap: .p-${spacing})`;
  } else if (base.match(/^px-/)) {
    const spacing = base.replace('px-', '');
    description = `Horizontal padding: ${spacing}`;
    bootstrapEquivalent = `(Bootstrap: .px-${spacing})`;
  } else if (base.match(/^py-/)) {
    const spacing = base.replace('py-', '');
    description = `Vertical padding: ${spacing}`;
    bootstrapEquivalent = `(Bootstrap: .py-${spacing})`;
  }
  
  // Sizing utilities
  else if (base.startsWith('w-')) {
    const width = base.replace('w-', '');
    if (width === 'full') {
      description = 'Full width (100%)';
      bootstrapEquivalent = '(Bootstrap: .w-100)';
    } else if (width.includes('/')) {
      description = `Width: ${width.replace('/', ' of ')}`;
      bootstrapEquivalent = '(Bootstrap: .w-* percentages)';
    } else {
      description = `Width: ${width}`;
      bootstrapEquivalent = '(Bootstrap: .w-*)';
    }
  } else if (base.startsWith('h-')) {
    const height = base.replace('h-', '');
    if (height === 'full') {
      description = 'Full height (100%)';
      bootstrapEquivalent = '(Bootstrap: .h-100)';
    } else {
      description = `Height: ${height}`;
      bootstrapEquivalent = '(Bootstrap: .h-*)';
    }
  }
  
  // Color utilities
  else if (base.startsWith('bg-')) {
    const color = base.replace('bg-', '').replace(/-/g, ' ');
    description = `Background: ${color}`;
    if (color.includes('blue')) bootstrapEquivalent = '(Bootstrap: .bg-primary)';
    else if (color.includes('green')) bootstrapEquivalent = '(Bootstrap: .bg-success)';
    else if (color.includes('red')) bootstrapEquivalent = '(Bootstrap: .bg-danger)';
    else if (color.includes('yellow')) bootstrapEquivalent = '(Bootstrap: .bg-warning)';
    else bootstrapEquivalent = '(Bootstrap: .bg-*)';
  } else if (base.startsWith('text-')) {
    // Check if it's a color or size
    if (base.match(/text-(xs|sm|base|lg|xl|2xl|3xl)/)) {
      const size = base.replace('text-', '');
      description = `Text size: ${size}`;
      bootstrapEquivalent = '(Bootstrap: .fs-*)';
    } else if (base.match(/text-(left|center|right)/)) {
      const align = base.replace('text-', '');
      description = `Text align: ${align}`;
      bootstrapEquivalent = `(Bootstrap: .text-${align})`;
    } else {
      const color = base.replace('text-', '').replace(/-/g, ' ');
      description = `Text color: ${color}`;
      if (color.includes('blue')) bootstrapEquivalent = '(Bootstrap: .text-primary)';
      else if (color.includes('green')) bootstrapEquivalent = '(Bootstrap: .text-success)';
      else if (color.includes('red')) bootstrapEquivalent = '(Bootstrap: .text-danger)';
      else if (color.includes('yellow')) bootstrapEquivalent = '(Bootstrap: .text-warning)';
      else bootstrapEquivalent = '(Bootstrap: .text-*)';
    }
  }
  
  // Typography utilities
  else if (base.startsWith('font-')) {
    const weight = base.replace('font-', '');
    description = `Font weight: ${weight}`;
    bootstrapEquivalent = `(Bootstrap: .fw-${weight})`;
  } else if (base === 'uppercase') {
    description = 'Transform text to uppercase';
    bootstrapEquivalent = '(Bootstrap: .text-uppercase)';
  } else if (base === 'lowercase') {
    description = 'Transform text to lowercase';
    bootstrapEquivalent = '(Bootstrap: .text-lowercase)';
  } else if (base === 'capitalize') {
    description = 'Capitalize first letter';
    bootstrapEquivalent = '(Bootstrap: .text-capitalize)';
  }
  
  // Border utilities
  else if (base === 'border') {
    description = 'Add border';
    bootstrapEquivalent = '(Bootstrap: .border)';
  } else if (base.startsWith('border-') && !base.includes('gray') && !base.includes('blue')) {
    const width = base.replace('border-', '');
    description = `Border width: ${width}`;
    bootstrapEquivalent = '(Bootstrap: .border-*)';
  } else if (base.startsWith('rounded')) {
    const radius = base.replace('rounded', '').replace(/-/g, ' ') || 'default';
    description = `Border radius: ${radius}`;
    bootstrapEquivalent = '(Bootstrap: .rounded*)';
  }
  
  // Effects
  else if (base.startsWith('shadow')) {
    const shadow = base.replace('shadow', '').replace(/-/g, ' ') || 'default';
    description = `Box shadow: ${shadow}`;
    bootstrapEquivalent = '(Bootstrap: .shadow*)';
  } else if (base.startsWith('opacity-')) {
    const opacity = base.replace('opacity-', '');
    description = `Opacity: ${opacity}%`;
    bootstrapEquivalent = '(Bootstrap: .opacity-*)';
  }
  
  // Position utilities
  else if (['static', 'relative', 'absolute', 'fixed', 'sticky'].includes(base)) {
    description = `Position: ${base}`;
    bootstrapEquivalent = `(Bootstrap: .position-${base})`;
  }
  
  // Default fallback
  else {
    description = `${base.charAt(0).toUpperCase() + base.slice(1).replace(/-/g, ' ')}`;
    bootstrapEquivalent = '';
  }

  // Add variant information
  let variantInfo = '';
  if (variant) {
    if (breakpoints.includes(variant)) {
      variantInfo = ` @${variant}+`;
    } else if (states.includes(variant)) {
      variantInfo = ` on ${variant}`;
    }
  }

  return `${cls} - ${description}${variantInfo} ${bootstrapEquivalent}`.trim();
}

function generateCssClassesJson() {
    try {
        // Define output paths
        const outputDir = path.join(__dirname, '..', 'public', 'assets');
        const outputPath = path.join(outputDir, 'tailwind-classes.json');
        
        // Ensure output directory exists
        if (!fs.existsSync(outputDir)) {
            fs.mkdirSync(outputDir, { recursive: true });
        }
        
        // Sort classes alphabetically for better searchability
        const sortedClasses = [...ALL_CSS_CLASSES].sort();
        
        // Create objects with value and descriptive text
        const describedClasses = sortedClasses.map(cls => ({
            value: cls,
            text: describeClass(cls)
        }));
        
        // Write JSON file
        fs.writeFileSync(outputPath, JSON.stringify(describedClasses, null, 2));
        
        console.log(`✅ Generated CSS classes JSON file:`);
        console.log(`   📁 ${outputPath}`);
        console.log(`   📊 ${describedClasses.length} CSS classes with descriptions exported`);
        
        // Also generate a summary file for debugging
        const summaryPath = path.join(outputDir, 'tailwind-classes-summary.txt');
        const summary = [
            `Essential Tailwind CSS Classes (Bootstrap 5 Equivalents)`,
            `Generated: ${new Date().toISOString()}`,
            `Total Classes: ${describedClasses.length}`,
            ``,
            `This curated list includes the most essential Tailwind classes that`,
            `provide Bootstrap 5 functionality without overwhelming users.`,
            ``,
            `Categories included:`,
            `- Layout & Grid (container, flexbox, grid)`,
            `- Spacing (margins, padding)`,
            `- Sizing (width, height)`,
            `- Colors (backgrounds, text)`,
            `- Typography (sizes, weights, alignment)`,
            `- Borders & Effects (radius, shadows)`,
            `- Position & Display utilities`,
            `- Responsive & Interactive states`,
            ``,
            `Sample Classes:`,
            ...describedClasses.slice(0, 25).map(cls => `  - ${cls.text}`),
            describedClasses.length > 25 ? `  ... and ${describedClasses.length - 25} more` : ''
        ].join('\n');
        
        fs.writeFileSync(summaryPath, summary);
        console.log(`   📋 Summary: ${summaryPath}`);
        
    } catch (error) {
        console.error('❌ Error generating CSS classes JSON:', error.message);
        process.exit(1);
    }
}

// Run the script
if (require.main === module) {
    generateCssClassesJson();
}

module.exports = { generateCssClassesJson };

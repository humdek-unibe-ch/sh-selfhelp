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

// Pattern/variant-based Tailwind CSS class generator
// Edit this list to match your frontend safelist logic
const PATTERN_SAFELIST = [
  // Colors (bg, text, border)
  {
    pattern: /^(bg|text|border)-(slate|gray|zinc|neutral|stone|red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose)-(50|100|200|300|400|500|600|700|800|900|950)$/,
    variants: ['', 'hover', 'focus', 'active', 'dark', 'dark:hover', 'dark:focus']
  },
  // Spacing
  {
    pattern: /^(p|m|px|py|pl|pr|pt|pb|mx|my|ml|mr|mt|mb)-(0|0.5|1|1.5|2|2.5|3|3.5|4|5|6|7|8|9|10|11|12|14|16|20|24|28|32|36|40|44|48|52|56|60|64|72|80|96)$/,
    variants: ['', 'sm', 'md', 'lg', 'xl', '2xl']
  },
  // Sizing
  {
    pattern: /^(w|h|min-w|min-h|max-w|max-h)-(0|0.5|1|1.5|2|2.5|3|3.5|4|5|6|7|8|9|10|11|12|14|16|20|24|28|32|36|40|44|48|52|56|60|64|72|80|96|auto|full|screen|min|max|fit)$/,
    variants: ['', 'sm', 'md', 'lg', 'xl', '2xl']
  },
  // Fractional sizing
  {
    pattern: /^(w|h)-(1\/2|1\/3|2\/3|1\/4|2\/4|3\/4|1\/5|2\/5|3\/5|4\/5|1\/6|2\/6|3\/6|4\/6|5\/6|1\/12|2\/12|3\/12|4\/12|5\/12|6\/12|7\/12|8\/12|9\/12|10\/12|11\/12)$/,
    variants: ['', 'sm', 'md', 'lg', 'xl', '2xl']
  },
  // Typography
  {
    pattern: /^text-(xs|sm|base|lg|xl|2xl|3xl|4xl|5xl|6xl|7xl|8xl|9xl)$/,
    variants: ['', 'sm', 'md', 'lg', 'xl', '2xl']
  },
  {
    pattern: /^font-(thin|extralight|light|normal|medium|semibold|bold|extrabold|black)$/,
    variants: ['']
  },
  // Border radius
  {
    pattern: /^rounded(-t|-r|-b|-l|-tl|-tr|-br|-bl)?(-none|-sm|-md|-lg|-xl|-2xl|-3xl|-full)?$/,
    variants: ['']
  },
  // Grid
  {
    pattern: /^grid-cols-(1|2|3|4|5|6|7|8|9|10|11|12|none)$/,
    variants: ['', 'sm', 'md', 'lg', 'xl', '2xl']
  },
  {
    pattern: /^col-(auto|span-1|span-2|span-3|span-4|span-5|span-6|span-7|span-8|span-9|span-10|span-11|span-12|span-full|start-1|start-2|start-3|start-4|start-5|start-6|start-7|start-8|start-9|start-10|start-11|start-12|start-13|start-auto|end-1|end-2|end-3|end-4|end-5|end-6|end-7|end-8|end-9|end-10|end-11|end-12|end-13|end-auto)$/,
    variants: ['', 'sm', 'md', 'lg', 'xl', '2xl']
  },
  // Gap
  {
    pattern: /^gap-(0|0.5|1|1.5|2|2.5|3|3.5|4|5|6|7|8|9|10|11|12|14|16|20|24|28|32|36|40|44|48|52|56|60|64|72|80|96|x-0|x-0.5|x-1|x-1.5|x-2|x-2.5|x-3|x-3.5|x-4|x-5|x-6|x-7|x-8|x-9|x-10|x-11|x-12|x-14|x-16|x-20|x-24|x-28|x-32|x-36|x-40|x-44|x-48|x-52|x-56|x-60|x-64|x-72|x-80|x-96|y-0|y-0.5|y-1|y-1.5|y-2|y-2.5|y-3|y-3.5|y-4|y-5|y-6|y-7|y-8|y-9|y-10|y-11|y-12|y-14|y-16|y-20|y-24|y-28|y-32|y-36|y-40|y-44|y-48|y-52|y-56|y-60|y-64|y-72|y-80|y-96)$/,
    variants: ['', 'sm', 'md', 'lg', 'xl', '2xl']
  },
  // Shadows
  {
    pattern: /^shadow-(none|sm|md|lg|xl|2xl|inner)$/,
    variants: ['', 'hover', 'focus', 'dark', 'dark:hover']
  },
  // Opacity
  {
    pattern: /^opacity-(0|5|10|20|25|30|40|50|60|70|75|80|90|95|100)$/,
    variants: ['', 'hover', 'focus', 'group-hover']
  },
  // Scale
  {
    pattern: /^scale-(0|50|75|90|95|100|105|110|125|150)$/,
    variants: ['', 'hover', 'focus', 'group-hover']
  },
  // Position/inset
  {
    pattern: /^(top|right|bottom|left|inset)-(0|0.5|1|1.5|2|2.5|3|3.5|4|5|6|7|8|9|10|11|12|14|16|20|24|28|32|36|40|44|48|52|56|60|64|72|80|96|auto|full)$/,
    variants: ['']
  },
  // Z-index
  {
    pattern: /^z-(0|10|20|30|40|50|auto)$/,
    variants: ['']
  }
];

// Utility to expand all pattern/variant combos into concrete class names
function expandPatterns(patternSafelist) {
  const values = new Set();

  // Helper to generate all combinations for a pattern/variant
  function expand(pattern, variants) {
    // Extract all possible group values from the regex
    // We'll parse the regex source for (foo|bar|baz) groups
    // and generate all combinations
    let src = pattern.source;
    // Find all (a|b|c) groups
    const groupMatches = [...src.matchAll(/\(([^)]+)\)/g)].map(m => m[1].split('|'));
    // If no groups, just use the pattern as-is
    if (groupMatches.length === 0) return;

    // Cartesian product of all groups
    function cartesian(arr) {
      return arr.reduce((a, b) => a.flatMap(d => b.map(e => d.concat([e]))), [[]]);
    }
    const combos = cartesian(groupMatches);
    combos.forEach(parts => {
      let base = src;
      let i = 0;
      // Replace each group with the chosen part
      base = base.replace(/\(([^)]+)\)/g, () => parts[i++]);
      // Remove ^ and $ from pattern
      base = base.replace(/^\^/, '').replace(/\$$/, '');
      variants.forEach(variant => {
        let cls = variant ? `${variant}:${base}` : base;
        // Remove duplicate :
        cls = cls.replace(/^:/, '');
        values.add(cls);
      });
    });
  }

  for (const entry of patternSafelist) {
    expand(entry.pattern, entry.variants);
  }
  return Array.from(values);
}

const ALL_CSS_CLASSES = expandPatterns(PATTERN_SAFELIST);

// Enhanced class description generator
function describeClass(cls) {
  const breakpoints = ['sm', 'md', 'lg', 'xl', '2xl'];
  const states = ['hover', 'focus', 'active', 'group-hover', 'dark', 'dark:hover', 'dark:focus'];

  const [maybeVariant, ...rest] = cls.split(':');
  const variant = breakpoints.includes(maybeVariant) || states.includes(maybeVariant) ? maybeVariant : '';
  const base = variant ? rest.join(':') : cls;

  let description = '';

  // Color utilities
  if (base.startsWith('bg-')) {
    const color = base.replace('bg-', '').replace(/-/g, ' ');
    description = `Background color: ${color}`;
  } else if (base.startsWith('text-')) {
    const color = base.replace('text-', '').replace(/-/g, ' ');
    description = `Text color: ${color}`;
  } else if (base.startsWith('border-')) {
    const color = base.replace('border-', '').replace(/-/g, ' ');
    description = `Border color: ${color}`;
  }
  // Spacing utilities
  else if (base.match(/^p[trblxy]?-/)) {
    const spacing = base.replace(/^p/, '').replace(/-/g, ' ');
    description = `Padding: ${spacing}`;
  } else if (base.match(/^m[trblxy]?-/)) {
    const spacing = base.replace(/^m/, '').replace(/-/g, ' ');
    description = `Margin: ${spacing}`;
  }
  // Sizing utilities
  else if (base.startsWith('w-')) {
    const width = base.replace('w-', '').replace(/-/g, ' ');
    description = `Width: ${width}`;
  } else if (base.startsWith('h-')) {
    const height = base.replace('h-', '').replace(/-/g, ' ');
    description = `Height: ${height}`;
  } else if (base.startsWith('min-w-')) {
    const minWidth = base.replace('min-w-', '').replace(/-/g, ' ');
    description = `Minimum width: ${minWidth}`;
  } else if (base.startsWith('min-h-')) {
    const minHeight = base.replace('min-h-', '').replace(/-/g, ' ');
    description = `Minimum height: ${minHeight}`;
  } else if (base.startsWith('max-w-')) {
    const maxWidth = base.replace('max-w-', '').replace(/-/g, ' ');
    description = `Maximum width: ${maxWidth}`;
  } else if (base.startsWith('max-h-')) {
    const maxHeight = base.replace('max-h-', '').replace(/-/g, ' ');
    description = `Maximum height: ${maxHeight}`;
  }
  // Grid utilities
  else if (base.startsWith('grid-cols-')) {
    const cols = base.replace('grid-cols-', '');
    description = `Grid columns: ${cols}`;
  } else if (base.startsWith('col-span-')) {
    const span = base.replace('col-span-', '');
    description = `Grid column span: ${span}`;
  } else if (base.startsWith('col-start-')) {
    const start = base.replace('col-start-', '');
    description = `Grid column start: ${start}`;
  } else if (base.startsWith('col-end-')) {
    const end = base.replace('col-end-', '');
    description = `Grid column end: ${end}`;
  } else if (base === 'col-auto') {
    description = 'Grid column: auto sizing';
  }
  // Typography utilities
  else if (base.startsWith('text-')) {
    const size = base.replace('text-', '').replace(/-/g, ' ');
    description = `Text size: ${size}`;
  } else if (base.startsWith('font-')) {
    const weight = base.replace('font-', '').replace(/-/g, ' ');
    description = `Font weight: ${weight}`;
  }
  // Border utilities
  else if (base.startsWith('rounded')) {
    const radius = base.replace('rounded', '').replace(/-/g, ' ') || 'default';
    description = `Border radius: ${radius}`;
  }
  // Gap utilities
  else if (base.startsWith('gap-')) {
    const gap = base.replace('gap-', '').replace(/-/g, ' ');
    description = `Grid gap: ${gap}`;
  }
  // Shadow utilities
  else if (base.startsWith('shadow-')) {
    const shadow = base.replace('shadow-', '').replace(/-/g, ' ');
    description = `Box shadow: ${shadow}`;
  }
  // Opacity utilities
  else if (base.startsWith('opacity-')) {
    const opacity = base.replace('opacity-', '');
    description = `Opacity: ${opacity}%`;
  }
  // Scale utilities
  else if (base.startsWith('scale-')) {
    const scale = base.replace('scale-', '');
    description = `Transform scale: ${scale}%`;
  }
  // Position utilities
  else if (base.match(/^(top|bottom|left|right|inset)-/)) {
    const [dir, val] = base.split('-');
    const direction = dir.charAt(0).toUpperCase() + dir.slice(1);
    description = `${direction} position: ${val}`;
  }
  // Z-index utilities
  else if (base.startsWith('z-')) {
    const zIndex = base.replace('z-', '');
    description = `Z-index: ${zIndex}`;
  }
  // Default fallback
  else {
    description = `Tailwind utility: ${base}`;
  }

  // Add variant information
  if (variant) {
    if (breakpoints.includes(variant)) {
      description += ` (${variant} breakpoint)`;
    } else if (states.includes(variant)) {
      description += ` (${variant} state)`;
    } else {
      description += ` (${variant} variant)`;
    }
  }

  return `${cls} - ${description}`;
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
            `CSS Classes Export Summary`,
            `Generated: ${new Date().toISOString()}`,
            `Total Classes: ${describedClasses.length}`,
            ``,
            `Sample Classes:`,
            ...describedClasses.slice(0, 20).map(cls => `  - ${cls.text}`),
            describedClasses.length > 20 ? `  ... and ${describedClasses.length - 20} more` : ''
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

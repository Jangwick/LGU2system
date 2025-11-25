# LRMS Design System Documentation

## Overview
This document outlines the complete Tailwind CSS design system used throughout the Legislative Records Management System (LRMS). All components, utilities, and styles are built with Tailwind CSS v4 for consistency and maintainability.

---

## Color Palette

### Primary Colors
- **Blue 50**: `bg-blue-50` - Light backgrounds
- **Blue 100**: `bg-blue-100` - Badge backgrounds
- **Blue 600**: `bg-blue-600` - Primary buttons, accents
- **Blue 700**: `bg-blue-700` - Hover states
- **Blue 800**: `bg-blue-800` - Sidebar, headers

### Status Colors
- **Success (Green)**: `bg-green-600`, `text-green-800`, `bg-green-100`
- **Warning (Yellow)**: `bg-yellow-600`, `text-yellow-800`, `bg-yellow-100`
- **Danger (Red)**: `bg-red-600`, `text-red-800`, `bg-red-100`
- **Info (Indigo)**: `bg-indigo-600`, `text-indigo-800`, `bg-indigo-100`
- **Secondary (Gray)**: `bg-gray-600`, `text-gray-800`, `bg-gray-100`

### Neutral Colors
- **Gray 50-100**: Background variations
- **Gray 300-500**: Borders, disabled states
- **Gray 600-900**: Text variations

---

## Typography

### Headings
```html
<!-- Page Title -->
<h1 class="text-3xl font-bold text-gray-800 mb-2">Page Title</h1>

<!-- Section Title -->
<h2 class="text-2xl font-bold text-gray-800 mb-4">Section Title</h2>

<!-- Subsection Title -->
<h3 class="text-lg font-bold text-gray-800 mb-4">Subsection Title</h3>

<!-- Card Title -->
<h4 class="text-lg font-semibold text-gray-900">Card Title</h4>
```

### Body Text
```html
<!-- Regular Text -->
<p class="text-gray-600">Regular body text</p>

<!-- Small Text -->
<p class="text-sm text-gray-600">Small text</p>

<!-- Extra Small Text -->
<p class="text-xs text-gray-500">Extra small text</p>

<!-- Bold Text -->
<p class="font-semibold text-gray-900">Bold text</p>
```

---

## Button Components

### Primary Button
```html
<button class="btn-primary">
    <i class="bi bi-plus-circle mr-2"></i>
    Primary Action
</button>
```
**Classes**: `bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 ease-in-out shadow-md hover:shadow-lg`

### Secondary Button
```html
<button class="btn-secondary">
    Secondary Action
</button>
```
**Classes**: `bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 ease-in-out`

### Success Button
```html
<button class="btn-success">
    <i class="bi bi-check-circle mr-2"></i>
    Success Action
</button>
```
**Classes**: `bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 ease-in-out`

### Danger Button
```html
<button class="btn-danger">
    <i class="bi bi-trash mr-2"></i>
    Delete
</button>
```
**Classes**: `bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 ease-in-out`

### Outline Button
```html
<button class="btn-outline">
    <i class="bi bi-download mr-2"></i>
    Export
</button>
```
**Classes**: `border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white font-semibold py-2 px-4 rounded-lg transition duration-200 ease-in-out`

---

## Form Elements

### Input Field
```html
<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Field Label
    </label>
    <input type="text" 
           placeholder="Enter value..." 
           class="input-field">
</div>
```
**Input Classes**: `w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent`

### Select Dropdown
```html
<select class="input-field">
    <option value="">Select option...</option>
    <option value="1">Option 1</option>
    <option value="2">Option 2</option>
</select>
```

### Textarea
```html
<textarea class="input-field" 
          rows="4" 
          placeholder="Enter description..."></textarea>
```

### Checkbox
```html
<label class="flex items-center">
    <input type="checkbox" 
           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
    <span class="ml-2 text-sm text-gray-700">Checkbox Label</span>
</label>
```

### Radio Button
```html
<label class="flex items-center">
    <input type="radio" 
           name="option" 
           class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-2 focus:ring-blue-500">
    <span class="ml-2 text-sm text-gray-700">Radio Option</span>
</label>
```

---

## Card Components

### Standard Card
```html
<div class="bg-white rounded-xl shadow-md p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Card Title</h3>
    <p class="text-gray-600">Card content goes here</p>
</div>
```

### Card with Header
```html
<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-900">Card Header</h3>
    </div>
    <div class="p-6">
        <p class="text-gray-600">Card body content</p>
    </div>
</div>
```

### Statistics Card
```html
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex items-center">
        <div class="flex-shrink-0">
            <i class="bi bi-file-earmark-text-fill text-blue-600 text-4xl"></i>
        </div>
        <div class="ml-4">
            <div class="text-sm text-gray-600">Metric Label</div>
            <div class="text-2xl font-bold text-gray-900">1,234</div>
        </div>
    </div>
</div>
```

---

## Badge Components

### Status Badges
```html
<!-- Success -->
<span class="badge badge-success">
    <i class="bi bi-check-circle mr-1"></i>Approved
</span>

<!-- Warning -->
<span class="badge badge-warning">
    <i class="bi bi-clock mr-1"></i>Pending
</span>

<!-- Danger -->
<span class="badge badge-danger">
    <i class="bi bi-x-circle mr-1"></i>Rejected
</span>

<!-- Primary -->
<span class="badge badge-primary">
    <i class="bi bi-info-circle mr-1"></i>Draft
</span>

<!-- Info -->
<span class="badge badge-info">
    <i class="bi bi-star mr-1"></i>Featured
</span>
```

**Badge Base**: `inline-flex items-center px-3 py-1 rounded-full text-sm font-medium`

---

## Table Components

### Standard Table
```html
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    Column Header
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    Cell Content
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

### Table with Actions
```html
<td class="px-6 py-4 whitespace-nowrap text-sm">
    <button class="text-blue-600 hover:text-blue-700 mr-2">
        <i class="bi bi-eye"></i>
    </button>
    <button class="text-green-600 hover:text-green-700 mr-2">
        <i class="bi bi-pencil"></i>
    </button>
    <button class="text-red-600 hover:text-red-700">
        <i class="bi bi-trash"></i>
    </button>
</td>
```

---

## Layout Components

### Page Header
```html
<div class="bg-white rounded-xl shadow-md p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Page Title</h1>
            <p class="text-gray-600">Page description or subtitle</p>
        </div>
        <div class="flex gap-3">
            <button class="btn-primary">Primary Action</button>
            <button class="btn-outline">Secondary Action</button>
        </div>
    </div>
</div>
```

### Filter Section
```html
<div class="bg-white rounded-xl shadow-md p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Filter fields -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Filter Label</label>
            <select class="input-field">
                <option>All</option>
            </select>
        </div>
    </div>
</div>
```

### Content Container
```html
<main class="flex-1 overflow-y-auto bg-gray-100 p-6">
    <!-- Page content -->
</main>
```

---

## Modal Components

### Standard Modal
```html
<div id="modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white">
        <!-- Modal Header -->
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-900">Modal Title</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="mb-4">
            <p class="text-gray-600">Modal content goes here</p>
        </div>
        
        <!-- Modal Footer -->
        <div class="flex justify-end gap-3">
            <button onclick="closeModal()" class="btn-secondary">
                Cancel
            </button>
            <button class="btn-primary">
                Confirm
            </button>
        </div>
    </div>
</div>
```

---

## Alert Components

### Success Alert
```html
<div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-4">
    <div class="flex items-center">
        <i class="bi bi-check-circle-fill text-green-600 mr-3"></i>
        <span>Success message here</span>
    </div>
</div>
```

### Error Alert
```html
<div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-4">
    <div class="flex items-center">
        <i class="bi bi-exclamation-circle-fill text-red-600 mr-3"></i>
        <span>Error message here</span>
    </div>
</div>
```

### Warning Alert
```html
<div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg mb-4">
    <div class="flex items-center">
        <i class="bi bi-exclamation-triangle-fill text-yellow-600 mr-3"></i>
        <span>Warning message here</span>
    </div>
</div>
```

### Info Alert
```html
<div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg mb-4">
    <div class="flex items-center">
        <i class="bi bi-info-circle-fill text-blue-600 mr-3"></i>
        <span>Information message here</span>
    </div>
</div>
```

---

## Grid Layouts

### 2-Column Grid
```html
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>Column 1</div>
    <div>Column 2</div>
</div>
```

### 3-Column Grid
```html
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <div>Column 1</div>
    <div>Column 2</div>
    <div>Column 3</div>
</div>
```

### 4-Column Grid (Statistics)
```html
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <div>Stat Card 1</div>
    <div>Stat Card 2</div>
    <div>Stat Card 3</div>
    <div>Stat Card 4</div>
</div>
```

---

## Icon Usage

### Bootstrap Icons
All icons use Bootstrap Icons library (https://icons.getbootstrap.com/)

**Common Icons:**
- File: `bi-file-earmark-text`
- Upload: `bi-cloud-upload`, `bi-upload`
- Download: `bi-download`
- Edit: `bi-pencil`, `bi-pencil-square`
- Delete: `bi-trash`, `bi-trash3`
- View: `bi-eye`
- Search: `bi-search`
- Filter: `bi-funnel`
- Settings: `bi-gear`
- User: `bi-person`, `bi-person-circle`
- Check: `bi-check-circle`, `bi-check-circle-fill`
- Close: `bi-x-circle`, `bi-x-lg`
- Info: `bi-info-circle`, `bi-info-circle-fill`
- Warning: `bi-exclamation-triangle`, `bi-exclamation-circle`
- Dashboard: `bi-speedometer2`
- Reports: `bi-graph-up`, `bi-bar-chart`

**Icon Sizing:**
- Small: `text-sm` or no class
- Medium: `text-lg` or `text-xl`
- Large: `text-2xl` or `text-3xl`
- Extra Large: `text-4xl` or larger

---

## Spacing System

### Margin/Padding Scale
- `0`: 0px
- `1`: 0.25rem (4px)
- `2`: 0.5rem (8px)
- `3`: 0.75rem (12px)
- `4`: 1rem (16px)
- `5`: 1.25rem (20px)
- `6`: 1.5rem (24px)
- `8`: 2rem (32px)
- `12`: 3rem (48px)

### Common Patterns
```html
<!-- Section Spacing -->
<div class="mb-6">Section</div>

<!-- Card Padding -->
<div class="p-6">Card content</div>

<!-- Button Spacing -->
<div class="flex gap-3">Buttons</div>

<!-- Grid Gap -->
<div class="grid gap-6">Grid items</div>
```

---

## Shadow System

### Shadow Utilities
- `shadow-sm`: Subtle shadow
- `shadow-md`: Medium shadow (cards)
- `shadow-lg`: Large shadow (modals)
- `shadow-xl`: Extra large shadow
- `shadow-2xl`: Massive shadow

### Hover Effects
```html
<div class="shadow-md hover:shadow-lg transition">
    Lift on hover
</div>
```

---

## Border Radius

### Rounded Corners
- `rounded`: 0.25rem (small)
- `rounded-lg`: 0.5rem (medium)
- `rounded-xl`: 0.75rem (large - cards)
- `rounded-2xl`: 1rem (extra large - headers)
- `rounded-full`: Full circle (badges, avatars)

---

## Transitions

### Standard Transition
```html
<button class="transition duration-200 ease-in-out">
    Smooth transition
</button>
```

### Hover Transitions
```html
<div class="hover:shadow-lg transition">
    Shadow transition
</div>

<button class="hover:bg-blue-700 transition">
    Background transition
</button>
```

---

## Responsive Design

### Breakpoints
- `sm`: 640px
- `md`: 768px
- `lg`: 1024px
- `xl`: 1280px
- `2xl`: 1536px

### Usage Examples
```html
<!-- Hide on mobile, show on desktop -->
<div class="hidden md:block">Desktop only</div>

<!-- Stack on mobile, side-by-side on desktop -->
<div class="flex flex-col md:flex-row">
    <div>Item 1</div>
    <div>Item 2</div>
</div>

<!-- Full width on mobile, fixed width on desktop -->
<div class="w-full md:w-96">
    Responsive width
</div>
```

---

## Custom Animations

### Fade In
```html
<div class="animate-fade-in">
    Fades in on load
</div>
```

### Slide In
```html
<div class="animate-slide-in">
    Slides in from right
</div>
```

### Pulse
```html
<div class="animate-pulse">
    Loading state
</div>
```

### Spin (Loading)
```html
<i class="bi bi-arrow-repeat animate-spin"></i>
```

---

## Utility Classes

### Text Utilities
- `text-balance`: Balanced text wrapping
- `line-clamp-2`: Limit to 2 lines with ellipsis
- `line-clamp-3`: Limit to 3 lines with ellipsis
- `truncate`: Single line with ellipsis
- `uppercase`: Transform to uppercase
- `capitalize`: Capitalize first letter

### Display Utilities
- `hidden`: Display none
- `block`: Display block
- `inline-block`: Display inline-block
- `flex`: Display flex
- `grid`: Display grid

### Position Utilities
- `relative`: Position relative
- `absolute`: Position absolute
- `fixed`: Position fixed
- `sticky`: Position sticky

---

## Accessibility Guidelines

### Focus States
All interactive elements should have visible focus states:
```html
<button class="focus:outline-none focus:ring-2 focus:ring-blue-500">
    Accessible button
</button>
```

### ARIA Labels
```html
<button aria-label="Close modal">
    <i class="bi bi-x-lg"></i>
</button>
```

### Color Contrast
- Ensure text has sufficient contrast (WCAG AA: 4.5:1 for normal text)
- Use gray-700+ for body text on white backgrounds
- Use white text on dark backgrounds (blue-600+)

---

## Best Practices

### 1. **Consistency**
- Always use the predefined component classes (btn-primary, badge-success, etc.)
- Maintain consistent spacing (mb-6 for sections, p-6 for cards)
- Use the same shadow levels across similar components

### 2. **Responsive Design**
- Start with mobile-first approach
- Use responsive grid classes (grid-cols-1 md:grid-cols-2 lg:grid-cols-4)
- Test on multiple screen sizes

### 3. **Performance**
- Avoid inline styles when possible
- Use Tailwind's utility classes instead of custom CSS
- Leverage browser caching with CDN

### 4. **Maintainability**
- Document any custom utility classes
- Keep components modular and reusable
- Use semantic HTML elements

### 5. **Accessibility**
- Include proper ARIA labels
- Ensure keyboard navigation works
- Maintain color contrast ratios
- Add alt text to images

---

## Component Library Reference

### Page Structure Template
```html
<?php
session_start();
$pageTitle = 'Page Title';
$currentPage = 'page-slug';
include_once __DIR__ . '/../../core/layouts/header.php';
?>

<?php include_once __DIR__ . '/../../core/layouts/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <?php include_once __DIR__ . '/../../core/layouts/navbar.php'; ?>
    
    <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
        <!-- Page Header -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Page Title</h1>
            <p class="text-gray-600">Page description</p>
        </div>
        
        <!-- Content sections -->
    </main>
</div>

<?php include_once __DIR__ . '/../../core/layouts/footer.php'; ?>
```

---

## Print Styles

### Print-Friendly Elements
```html
<div class="no-print">
    <!-- Hidden in print -->
    <button>Action Button</button>
</div>

<div class="print-full-width">
    <!-- Full width in print -->
    <table>Content table</table>
</div>
```

---

## Version History

- **v1.0.0** - Initial design system
- Tailwind CSS v4
- Bootstrap Icons v1.11.1
- Chart.js (latest)

---

**Last Updated**: November 2025  
**Maintained By**: LRMS Development Team

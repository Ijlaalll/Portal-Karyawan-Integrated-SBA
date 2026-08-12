---
name: Core Enterprise Reserve
colors:
  surface: '#f8f9fa'
  surface-dim: '#d9dadb'
  surface-bright: '#f8f9fa'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f3f4f5'
  surface-container: '#edeeef'
  surface-container-high: '#e7e8e9'
  surface-container-highest: '#e1e3e4'
  on-surface: '#191c1d'
  on-surface-variant: '#434655'
  inverse-surface: '#2e3132'
  inverse-on-surface: '#f0f1f2'
  outline: '#737686'
  outline-variant: '#c3c6d7'
  surface-tint: '#0053db'
  primary: '#004ac6'
  on-primary: '#ffffff'
  primary-container: '#2563eb'
  on-primary-container: '#eeefff'
  inverse-primary: '#b4c5ff'
  secondary: '#555f6d'
  on-secondary: '#ffffff'
  secondary-container: '#d6e0f1'
  on-secondary-container: '#596372'
  tertiary: '#943700'
  on-tertiary: '#ffffff'
  tertiary-container: '#bc4800'
  on-tertiary-container: '#ffede6'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dbe1ff'
  primary-fixed-dim: '#b4c5ff'
  on-primary-fixed: '#00174b'
  on-primary-fixed-variant: '#003ea8'
  secondary-fixed: '#d9e3f4'
  secondary-fixed-dim: '#bdc7d8'
  on-secondary-fixed: '#121c28'
  on-secondary-fixed-variant: '#3e4755'
  tertiary-fixed: '#ffdbcd'
  tertiary-fixed-dim: '#ffb596'
  on-tertiary-fixed: '#360f00'
  on-tertiary-fixed-variant: '#7d2d00'
  background: '#f8f9fa'
  on-background: '#191c1d'
  surface-variant: '#e1e3e4'
typography:
  headline-lg:
    fontFamily: Inter
    fontSize: 30px
    fontWeight: '600'
    lineHeight: 38px
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-sm:
    fontFamily: Inter
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '500'
    lineHeight: 20px
  label-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  base: 8px
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 32px
  container-margin: 32px
  gutter: 16px
---

## Brand & Style
The design system is engineered for utility, efficiency, and professional reliability. It targets an enterprise environment where clarity and speed of task completion are paramount. The aesthetic follows a **Modern Corporate** approach—leaning heavily into functional minimalism. 

The UI prioritizes content over decoration, using white space and structured alignment to reduce cognitive load. The emotional response should be one of "quiet competence": an interface that stays out of the way until needed. There is no use of gradients, blurs, or heavy ornamentation; instead, the design system relies on a rigorous grid, consistent strokes, and a disciplined color application to convey hierarchy.

## Colors
This design system utilizes a high-clarity, light-mode palette. 

- **Primary**: Corporate Blue (#2563EB) is reserved strictly for primary calls to action, active states, and critical system indicators. 
- **Surface & Background**: The application background uses a very light gray (#F9FAFB) to differentiate the canvas from white (#FFFFFF) container surfaces like cards and sidebars.
- **Stroke & Border**: All structural divisions use a subtle gray (#E5E7EB). 
- **Semantic Colors**: Green and Red are used sparingly for room availability status (Available/Occupied) and destructive actions.
- **Text**: Use Slate-900 (#0F172A) for headings and Slate-600 (#475569) for secondary body text to maintain high contrast and readability.

## Typography
The typography is built entirely on **Inter**, a typeface optimized for screen readability and UI clarity. 

The scale is intentionally compact to accommodate data-heavy views. For desktop environments, `body-md` (14px) is the standard for data tables and form labels. `label-sm` should be used for table headers and category tags, appearing in uppercase with a medium or semi-bold weight. Paragraphs should use `body-lg` to ensure comfortable reading of room descriptions or policies. Mobile views should collapse `headline-lg` to `headline-md` sizes to prevent overflow.

## Layout & Spacing
This design system adheres to a strict 8px linear scale. All margins, padding, and component heights must be multiples of 8 (or 4 for micro-adjustments).

- **Grid**: A 12-column fluid grid is used for the main content area. 
- **Side Navigation**: A fixed-width sidebar (240px or 280px) remains docked to the left.
- **Data Tables**: Use 12px vertical padding (1.5 units) for rows to balance density and touch targets.
- **Breakpoints**: 
  - Mobile: < 640px (Single column, 16px margins)
  - Tablet: 640px - 1024px (Reduced sidebar or drawer)
  - Desktop: > 1024px (Fixed sidebar, 32px container margins)

## Elevation & Depth
Depth is conveyed through **Tonal Layering** and **Minimal Shadows**. 

- **Level 0**: Background (#F9FAFB).
- **Level 1**: Main content cards, sidebars, and white containers. These use a 1px solid border (#E5E7EB) and no shadow.
- **Level 2**: Overlays, dropdowns, and modals. These use a 1px border AND a soft, diffused shadow: `0px 4px 6px -1px rgba(0, 0, 0, 0.1), 0px 2px 4px -2px rgba(0, 0, 0, 0.05)`.

Avoid any "pop-out" effects. The interface should feel flat and structural, like a well-organized physical document.

## Shapes
The shape language is conservative and geometric. 

A **Soft (0.25rem / 4px)** corner radius is the standard for buttons, input fields, and small UI components. Larger containers like cards and modals may use **rounded-lg (0.5rem / 8px)** to subtly soften the layout without appearing playful. Icons should follow a 2px stroke weight with slight rounding to match the component language. Circular shapes are strictly reserved for user avatars and radio buttons.

## Components
- **Buttons**: Primary buttons are solid Blue (#2563EB) with white text. Secondary buttons are white with a Gray (#E5E7EB) border and dark gray text. Use a height of 36px for standard actions and 44px for primary mobile actions.
- **Input Fields**: Use a white background with a 1px border. On focus, the border changes to Primary Blue with a 1px inner blue ring. Place labels above the field in `label-md`.
- **Data Tables**: Row borders are 1px solid (#E5E7EB). Headers have a light gray background (#F9FAFB). Use "zebra-striping" only for tables exceeding 20 rows.
- **Chips/Badges**: For status indicators (e.g., "Confirmed", "Pending"), use a subtle tinted background (e.g., light green background with dark green text) with a high-degree of roundedness (pill-shaped).
- **Calendar**: The reservation calendar should use a clean grid with 1px borders. Active selections or time slots are indicated by a semi-transparent blue fill and a solid blue left-border.
- **Side Navigation**: Links use `label-md`. Active states are marked by a vertical 4px blue bar on the left edge and a light blue tinted background (#EFF6FF).
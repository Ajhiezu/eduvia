# Eduvia — UI/UX Design Memory for Antigravity

## Purpose
This file is the persistent design reference for the Eduvia e-learning and school-management platform. Read and follow it before creating or modifying any UI, UX, frontend component, page, layout, icon, or design token. This guidance applies across all phases and modules unless the user explicitly approves a change.

## Brand identity
- Product name: **Eduvia**
- Tagline: **Learn · Grow · Build Your Future**
- Product description: E-Learning and School Management Platform for Indonesian schools from SD, SMP, SMA, and SMK.
- Primary reference: user-provided image `Eduvia Education Brand Style Guide(1).png`.
- Use the exact user-provided logo assets wherever possible. Do not redraw, regenerate, approximate, or replace the official logo with a text-only imitation or a web-found logo.
- If the logo asset is not available as a separate file, inspect the project assets and the provided brand-guide image. Extract/crop the official logo only when it can be done cleanly without altering its proportions or appearance. Prefer asking the user for the original transparent PNG/SVG if a clean asset cannot be obtained.
- Never invent an alternative logo, symbol, or tagline. Do not alter logo colors, proportions, spacing, letterforms, or iconography.

## Official colors
Use these brand-guide values as the source of truth:
- Primary Red: `#E11D2E`
- Dark Text: `#1F2937`
- Light Gray: `#F3F4F6`

Suggested supporting neutrals may be introduced only when needed for accessible UI, and must not compete with the official palette:
- White surfaces: `#FFFFFF`
- Muted text: choose a neutral gray with sufficient contrast
- Borders/dividers: subtle neutral gray
- Success, warning, and error colors: semantic colors, used sparingly and never as replacements for the brand primary.

Do not silently change the three official colors. If existing design tokens conflict, consolidate them to these values and document the changes.

## Visual direction
- Professional, modern, trustworthy, clear, and education-focused.
- Clean layouts with generous but efficient spacing.
- Light backgrounds and white surfaces as the default.
- Use red primarily for primary actions, active states, important highlights, selected navigation, and restrained brand accents.
- Use dark text for readability.
- Avoid full-page red backgrounds except for intentional brand moments such as a splash/login treatment where appropriate.
- Avoid excessive gradients, glassmorphism, neon colors, decorative blobs, excessive shadows, and unnecessary animations.
- Avoid childish visuals that would make the product feel unsuitable for SMA/SMK administrators or teachers.
- Keep the experience friendly enough for younger students while still professional for teachers, parents, and school operators.
- Prefer consistent alignment, hierarchy, spacing, and predictable interactions over visual novelty.

## Logo and app icon rules
- Prefer the official horizontal logo in desktop headers where space allows.
- Use the official vertical logo in contexts where the vertical lockup fits better.
- Use the official app icon for favicon, PWA/app icon, and compact brand placement when the appropriate asset is available.
- Respect clear space and aspect ratio. Never stretch, crop, skew, recolor, or add effects to the logo.
- On small screens, use the official icon only if supplied and legible; otherwise use the official logo asset at a suitable size.
- Do not substitute emoji or a generic graduation-cap icon for the Eduvia logo.

## Typography and content
- Use the project's existing, readable font stack unless there is a clear reason to change it.
- Establish a consistent type scale for page titles, section headings, body text, labels, helper text, and metadata.
- Body text should generally be at least 14px on mobile; prioritize readability.
- Use concise, clear Bahasa Indonesia for the Indonesian user interface unless the user asks otherwise.
- Keep labels, button names, date formats, validation messages, and empty states consistent.
- Avoid truncating important text or wrapping short action labels awkwardly.

## Layout and responsive behavior
- Mobile-first and responsive across phone, tablet, laptop, and desktop.
- Test at widths 320, 360, 390, 430, 768, 1024, and 1440 px where tooling allows.
- Prevent unintended horizontal overflow, clipped content, overlapping controls, and blank horizontal space.
- Navigation should adapt to small screens without hiding essential actions.
- Forms, cards, tables, dialogs, menus, and dashboards must have deliberate responsive behavior.
- Tables may use responsive layouts or controlled horizontal scrolling when appropriate; the overall page must not overflow accidentally.
- Preserve user-entered data and interaction state when possible during responsive transitions.
- Ensure keyboard navigation, visible focus states, accessible labels, and adequate contrast.

## Component system
Build and reuse shared design-system components rather than styling every page independently:
- Buttons: primary, secondary, outline, subtle, danger, disabled, loading.
- Form controls: text input, textarea, select, checkbox, radio, switch, validation/help text.
- Surfaces: cards, panels, dialogs, drawers, popovers.
- Feedback: alert, toast, badge, progress, skeleton, empty state, error state.
- Data UI: table, pagination, filters, sorting, status indicators.
- Navigation: sidebar, top bar, breadcrumbs, tabs, mobile navigation.
- Data visualization: use restrained, accessible colors and do not rely on color alone to convey meaning.

All shared components must have consistent tokens, variants, hover/focus/active/disabled/loading states, and responsive behavior. Do not create duplicate components with slightly different styles when an existing shared component can be improved.

## Icons and imagery
- Reuse official Eduvia logo and icon assets from the repository or user-provided files.
- Prefer one consistent icon library already installed in the project. Do not mix multiple icon styles without a strong reason.
- Icons should be visually consistent in stroke weight and size.
- Do not use emoji as substitutes for interface icons.
- Use meaningful alt text for informative images and empty alt text for purely decorative images.
- Never use a generated or web-sourced logo in place of the official asset.

## UX rules
- Every interactive control must perform the action its appearance promises.
- Provide clear loading, success, empty, validation-error, permission-denied, and general-error states where relevant.
- Destructive actions require a clear confirmation pattern when appropriate.
- Form errors should explain how to fix the problem and appear close to the affected field.
- Do not rely on color alone for status or errors.
- Keep navigation and terminology consistent across roles and modules.
- Do not expose actions or data to users without permission; frontend visibility is not a substitute for backend authorization.
- Avoid unnecessary steps, surprise navigation, and inconsistent interaction patterns.
- Keep accessibility and usability in mind for students, teachers, parents, and administrators with different levels of technical confidence.

## Dashboard and role-specific UI
- Keep shared branding and component behavior consistent across all roles.
- Adapt navigation, dashboard content, and available actions to each user's role and permissions.
- Do not expose admin-oriented controls to students or parents.
- Avoid building every role's dashboard as an identical page with different labels; use role-relevant information while reusing the same design system.
- Keep dashboard density appropriate to the audience and device.

## Implementation rules for Antigravity / coding agents
Before UI work:
1. Inspect the existing repository, installed UI framework, design tokens, components, and asset directories.
2. Locate and reuse the official Eduvia logo/icon assets. Do not assume asset paths; verify them.
3. Read this memory file and any project-specific UI documentation.
4. Identify existing reusable components before creating new ones.
5. State the intended scope and files likely to change before making broad UI changes.

While implementing:
- Preserve existing business logic, routes, APIs, database schema, and functionality unless explicitly requested.
- Do not perform unrelated refactors.
- Do not redesign unrelated modules when asked to modify one module.
- Use the official color tokens and shared components.
- Keep changes focused, responsive, accessible, and testable.
- Do not install a new dependency without checking whether the project already has a suitable option.
- Do not claim an asset exists unless its path has been verified.
- Do not use placeholder logos or fabricated screenshots in production UI.

After implementing:
- Run the available lint, type checks, tests, and build.
- Inspect relevant pages at mobile and desktop widths where tooling allows.
- Check for broken assets, console errors, overflow, clipped text, contrast issues, and inconsistent states.
- Report what was changed, files touched, checks run, results, and any unresolved issue.
- If a requested requirement cannot be verified, state that explicitly rather than claiming “no mistakes.”

## Definition of done for UI changes
A UI change is complete only when:
- It follows Eduvia's official brand identity and colors.
- It reuses official logo/icon assets where applicable.
- It is responsive and has no unintended overflow.
- Text remains readable and important labels are not clipped or awkwardly wrapped.
- Loading, empty, error, disabled, and permission states are handled when relevant.
- Keyboard focus and accessible labels are considered.
- Existing functionality remains intact.
- Available checks have been run and their results accurately reported.

## Change control
This design memory is the default standard for all future Eduvia UI/UX work. Do not change the logo, tagline, official colors, overall visual direction, or component conventions without explicit user approval. If a genuine conflict arises between this memory and the existing product, explain the conflict and propose the smallest safe resolution.

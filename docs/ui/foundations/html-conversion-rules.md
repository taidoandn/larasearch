# HTML Conversion Rules

## Purpose of code.html
- code.html is a visual and structural Tailwind reference
- it is not the final architecture
- do not blindly paste it into a page component

## Required conversion
The agent must:
- extract reusable sections
- identify repeated UI patterns
- convert static markup into React components
- convert duplicated blocks into mapped arrays/components
- normalize class usage where practical

## Forbidden
- Do not keep one giant page component with all HTML inline
- Do not duplicate the same card markup multiple times manually
- Do not preserve placeholder copy that conflicts with Larasearch branding

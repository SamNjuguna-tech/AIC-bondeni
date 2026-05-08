## Tech Stack

- Next.js 15
- TypeScript
- Firebase
- TailwindCSS
- Node.js 22

## AI Assistant Instructions

When making changes:
1. Read related files first
2. Preserve existing patterns
3. Do not introduce new dependencies unless necessary
4. Explain major architectural changes
5. Prefer incremental edits over rewrites

## UI/UX Guidelines

- Mobile-first design
- Rounded corners: `rounded-2xl`
- Avoid modals for critical flows
- Maintain accessibility standards

## API Guidelines

- Validate all request bodies with Zod
- Return typed responses
- Use async/await only

## Database Rules

Firestore collections:
[To be decided]

Do not rename collections without migration scripts.

## Authentication

Firebase Authentication is used (RBC).

Important:
- Never expose admin SDK credentials
- Client auth logic lives in `/lib/auth`

## Coding Conventions

- Use functional React components only
- Prefer server components unless interactivity is required
- Use TypeScript strictly
- Avoid `any`
- Use Tailwind utility classes
- Keep components under 250 lines where possible

## Project Structure

/app
/components
/lib
/hooks
/API

Important areas:
- `/lib/firebase` contains Firebase config
- `/API` contains API logic
- `/components/ui` contains reusable UI primitives

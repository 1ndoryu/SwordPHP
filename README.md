# Sword v2: Minimalist Headless CMS

**Sword v2** is a complete rewrite of a CMS, built from the ground up on **Workerman** to be extremely fast, lightweight, and easy to maintain.

It operates as a pure headless API, completely decoupling the logic from the administration panel. Its architecture relies on a simple database schema (`contents`, `users`, `media`, `options`, `comments`, `likes`) that uses `JSONB` to provide maximum flexibility and performance. The goal is to create a powerful core that never becomes complex again.

## Core Principles

1.  **Extreme Simplification:** Code must be simple, readable, and professional.
2.  **Code Standards:** All code is written in **English** using the `snake_case` convention.
3.  **Pure & Decoupled API:** The CMS is exclusively an API responsible for content, authentication, and files.
4.  **Mandatory Testing:** 100% of the code must be testable.
5.  **Clean Code:** Code should be self-explanatory, minimizing the need for comments.
6.  **Concise Documentation:** A single, clear document explaining the API usage.
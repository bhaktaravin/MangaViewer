# MangaView Project Setup

## Project Overview
MangaView is a Laravel-based web application that combines user authentication with a personal manga reader. The project uses SQLite for database storage and Laravel Breeze for authentication.

## Project Structure

### Models
1. **User** - Default Laravel user model for authentication
2. **Manga** - Stores manga information (title, description, cover image, etc.)
3. **Chapter** - Stores chapter information for each manga
4. **Page** - Stores individual page images for each chapter

### Controllers
1. **ProfileController** - Handles user profile management (from Laravel Breeze)
2. **MangaController** - Manages manga CRUD operations
3. **ChapterController** - Manages chapter CRUD operations
4. **PageController** - Manages page CRUD operations

### Database Schema

#### Manga Table
- id (primary key)
- title (string)
- description (text, nullable)
- cover_image (string, nullable)
- author (string, nullable)
- status (string, default: 'ongoing')
- total_chapters (integer, default: 0)
- timestamps

#### Chapter Table
- id (primary key)
- manga_id (foreign key)
- title (string)
- chapter_number (integer)
- content (text, nullable)
- file_path (string, nullable)
- timestamps

#### Page Table
- id (primary key)
- chapter_id (foreign key)
- page_number (integer)
- image_path (string)
- timestamps

## Routes

### Authentication Routes
- Standard Laravel Breeze authentication routes

### Manga Routes
- Public routes:
  - GET /manga - List all manga
  - GET /manga/{manga} - View a specific manga
  - GET /manga/{manga}/chapters/{chapter} - View a specific chapter

- Authenticated routes:
  - All CRUD operations for manga, chapters, and pages
  - Nested resource routes for proper relationship management

## Next Steps

1. Create Blade views for all controllers
2. Implement file upload functionality for manga covers and chapter pages
3. Add user permissions/roles to restrict manga management
4. Implement a reading progress tracking system
5. Add search and filtering functionality
6. Create a user library/bookmarks system

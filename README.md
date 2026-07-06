# Kifa’a - Digital Twin Career Platform

Kifa’a is a Laravel-based Digital Twin career platform that helps users analyze their CV, extract skills, identify missing market skills, recommend courses, simulate skill impact, and track career readiness.

The platform supports students and job seekers by building a career profile based on their CV or manually entered information. It then creates a Digital Twin dashboard that shows the user’s current skills, missing skills, readiness score, learning progress, and recommended courses.

---

## Table of Contents

- [Project Overview](#project-overview)
- [Features](#features)
- [Technologies Used](#technologies-used)
- [Requirements](#requirements)
- [Installation](#installation)
- [Environment Variables](#environment-variables)
- [Running the Project](#running-the-project)
- [Supported CV File Types](#supported-cv-file-types)
- [Main User Flow](#main-user-flow)
- [AI Features](#ai-features)
- [Weekly Job Market Skills Sync](#weekly-job-market-skills-sync)
- [Course Recommendations](#course-recommendations)
- [Skill Impact Simulation](#skill-impact-simulation)
- [Useful Commands](#useful-commands)
- [Troubleshooting](#troubleshooting)
- [Project Structure](#project-structure)
- [Important Notes](#important-notes)
- [License](#license)

---

## Project Overview

Kifa’a is designed to help users understand their career readiness by analyzing their skills and comparing them with market needs.

The system allows the user to upload a CV file or enter career profile information manually. After analysis, the platform extracts the user’s job title, skills, and career interests. Then it generates a Digital Twin profile that represents the user’s current career status.

The Digital Twin dashboard helps the user identify:

- Current skills
- Missing skills
- Newly in-demand market skills
- Career readiness score
- Skill statistics
- Recommended learning resources
- Completed skills and progress

---

## Features

- User registration and login
- CV upload and analysis
- PDF and DOCX CV support
- AI-based job title extraction
- AI-based skill extraction
- Manual career profile creation
- Profile preview and confirmation
- Career profile editing
- CV re-analysis
- Digital Twin dashboard
- Digital Twin readiness score
- Skill statistics and charts
- Missing skill detection
- Weekly job market skills sync
- Newly in-demand skill badge
- Course recommendations for missing skills
- Direct course links
- Course completion tracking
- Skill impact simulation
- Settings page
- Light mode and dark mode
- Responsive user interface

---

## Technologies Used

- Laravel
- PHP
- MySQL
- Blade
- Livewire / Volt
- Flux UI
- Tailwind CSS
- Vite
- JavaScript
- OpenAI API
- Composer
- npm
- Git

---

## Requirements

Before running the project, make sure the following tools are installed:

- PHP 8.2 or higher
- Composer
- Node.js
- npm
- MySQL
- Git

---

## Installation

### 1. Clone or download the project

If you are using Git:

```bash
git clone https://github.com/leno-7/kifaa.git
```

Then open the project folder:

```bash
cd kifaa
```

If the project is downloaded as a ZIP file, extract it first, then open the extracted folder in VS Code or in the terminal.

Make sure the terminal is inside the main project folder where these files exist:

```text
artisan
composer.json
package.json
.env.example
```

---

### 2. Install PHP dependencies

```bash
composer install
```

---

### 3. Install frontend dependencies

```bash
npm install
```

---

### 4. Create the environment file

For macOS or Linux:

```bash
cp .env.example .env
```

For Windows:

```bash
copy .env.example .env
```

---

### 5. Generate the application key

```bash
php artisan key:generate
```

---

### 6. Configure the database

Create a MySQL database.

Example database name:

```sql
CREATE DATABASE Kifaa;
```

Then open the `.env` file and update the database settings:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=Kifaa
DB_USERNAME=root
DB_PASSWORD=
```

Update `DB_USERNAME` and `DB_PASSWORD` based on your local MySQL setup.

---

### 7. Configure OpenAI API keys

The project uses OpenAI for CV analysis, market skill analysis, weekly job market skill sync, and course recommendations.

Add the following values to the `.env` file:

```env
OPENAI_CV_KEY=your_openai_key_here
OPENAI_MARKET_KEY=your_openai_key_here
OPENAI_JOB_MARKET_KEY=your_openai_key_here
OPENAI_COURSE_RECOMMENDATION_KEY=your_openai_key_here

OPENAI_MODEL=gpt-4.1-mini
OPENAI_URL=https://api.openai.com/v1/responses
```

Do not share real OpenAI keys inside GitHub, `.env.example`, public ZIP files, screenshots, or source code files.

---

### 8. Run database migrations

```bash
php artisan migrate
```

If the project includes seeders, run:

```bash
php artisan migrate --seed
```

---

### 9. Create the storage link

```bash
php artisan storage:link
```

---

## Environment Variables

The real `.env` file is not included in the repository because it contains private configuration and API keys.

Use `.env.example` as a template, then add your local values.

Example `.env` configuration:

```env
APP_NAME=Kifaa
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=Kifaa
DB_USERNAME=root
DB_PASSWORD=

OPENAI_CV_KEY=your_openai_key_here
OPENAI_MARKET_KEY=your_openai_key_here
OPENAI_JOB_MARKET_KEY=your_openai_key_here
OPENAI_COURSE_RECOMMENDATION_KEY=your_openai_key_here

OPENAI_MODEL=gpt-4.1-mini
OPENAI_URL=https://api.openai.com/v1/responses
```

### OpenAI key usage

| Key | Purpose |
|---|---|
| `OPENAI_CV_KEY` | Used for CV analysis, job title extraction, and skill extraction |
| `OPENAI_MARKET_KEY` | Used for market skill analysis and missing skill support |
| `OPENAI_JOB_MARKET_KEY` | Used for weekly job market skill synchronization |
| `OPENAI_COURSE_RECOMMENDATION_KEY` | Used for generating course recommendations for missing skills |

---

## Running the Project

Open two terminal windows inside the project folder.

### Terminal 1: Start the frontend development server

```bash
npm run dev
```

Keep this terminal running.

### Terminal 2: Start the Laravel development server

```bash
php artisan serve
```

Then open the project in the browser:

```text
http://127.0.0.1:8000
```

---

## Build for Production

To build frontend assets for production:

```bash
npm run build
```

---

## Supported CV File Types

The system supports:

- PDF
- DOCX

---

## Main User Flow

1. The user registers or logs in.
2. The user uploads a CV file or creates the career profile manually.
3. The system analyzes the CV or submitted profile data.
4. The system extracts the job title, skills, and career-related information.
5. The user reviews the extracted information in the preview page.
6. The user confirms the career profile.
7. The Digital Twin dashboard is generated.
8. The dashboard displays current skills, missing skills, readiness score, and statistics.
9. The user can open recommended courses for missing skills.
10. The user can simulate the impact of learning a missing skill.
11. The user can mark a skill or course as completed.
12. The user can edit the career profile or re-analyze the CV when needed.
13. The weekly job market sync updates newly in-demand missing skills.

---

## AI Features

Kifa’a uses AI to support the main career analysis features.

AI is used for:

- CV content analysis
- Job title extraction
- Skill extraction
- Market skill generation
- Missing skill detection support
- Weekly job market skill updates
- Course recommendation generation
- Filtering and improving recommended learning resources

The AI features require valid OpenAI API keys in the `.env` file.

Without valid OpenAI keys, the application can still run, but AI-based features such as CV analysis, market skill generation, weekly sync, and course recommendations will not work correctly.

---

## Weekly Job Market Skills Sync

The system includes a weekly job market sync feature.

Every week, Kifa’a checks the user’s selected career interest, searches for newly in-demand skills in the job market, and compares them with the user’s current skills and existing missing skills.

If a skill is new and the user does not already have it, the system adds it to the missing skills list and marks it as:

```text
Newly in demand
```

The sync command is:

```bash
php artisan skills:sync-job-market
```

The command is scheduled in `routes/console.php`:

```php
Schedule::command('skills:sync-job-market')->weeklyOn(1, '09:00');
```

This runs the sync every Monday at 9:00 AM.

To test the scheduler locally, run:

```bash
php artisan schedule:work
```

---

## Course Recommendations

The course recommendation feature is based on the user’s missing skills.

When the user selects a missing skill from the Digital Twin dashboard, the system generates recommended courses related to that skill. The goal is to help the user improve the missing skill through practical learning resources.

The recommendations may include:

- Course title
- Course provider
- Course link
- Short description
- Skill relevance

The course recommendation feature uses:

```env
OPENAI_COURSE_RECOMMENDATION_KEY=your_openai_key_here
```

---

## Skill Impact Simulation

Kifa’a includes a skill impact simulation feature.

This feature helps the user understand how learning a missing skill may improve their Digital Twin readiness score and career profile.

The simulation gives the user a clearer idea of which skills may have a stronger effect on their progress.

---

## Useful Commands

Clear Laravel cache:

```bash
php artisan optimize:clear
```

Run migrations:

```bash
php artisan migrate
```

Run migrations from scratch:

```bash
php artisan migrate:fresh
```

Run migrations from scratch with seeders:

```bash
php artisan migrate:fresh --seed
```

Start Laravel server:

```bash
php artisan serve
```

Start Vite development server:

```bash
npm run dev
```

Build frontend assets:

```bash
npm run build
```

Create storage link:

```bash
php artisan storage:link
```

Run weekly job market sync manually:

```bash
php artisan skills:sync-job-market
```

Run Laravel scheduler locally:

```bash
php artisan schedule:work
```

---

## Troubleshooting

### 1. Composer dependencies are missing

Run:

```bash
composer install
```

---

### 2. Node modules are missing

Run:

```bash
npm install
```

---

### 3. Application key is missing

Run:

```bash
php artisan key:generate
```

---

### 4. Database tables are missing

Run:

```bash
php artisan migrate
```

Or reset and recreate the database tables:

```bash
php artisan migrate:fresh
```

---

### 5. Uploaded CV files are not accessible

Run:

```bash
php artisan storage:link
```

---

### 6. Changes are not showing

Clear Laravel cache:

```bash
php artisan optimize:clear
```

Then refresh the browser.

---

### 7. AI features are not working

Make sure all OpenAI keys are added correctly in the `.env` file:

```env
OPENAI_CV_KEY=your_openai_key_here
OPENAI_MARKET_KEY=your_openai_key_here
OPENAI_JOB_MARKET_KEY=your_openai_key_here
OPENAI_COURSE_RECOMMENDATION_KEY=your_openai_key_here

OPENAI_MODEL=gpt-4.1-mini
OPENAI_URL=https://api.openai.com/v1/responses
```

Also make sure:

- The API keys are valid.
- The `.env` file is saved.
- Laravel cache is cleared after changing environment values.

Run:

```bash
php artisan optimize:clear
```

---

### 8. Vite assets are not loading

Run:

```bash
npm run dev
```

If preparing the project for production, run:

```bash
npm run build
```

---

## Project Structure

Important folders:

```text
app/
    Console/
        Commands/
    Http/
        Controllers/
    Livewire/
    Models/
    Notifications/
    Providers/
    Services/

resources/
    views/
    css/
    js/

routes/
    web.php
    console.php
    settings.php

database/
    migrations/
    factories/
    seeders/

public/
    images/

storage/
```

Main project logic is organized into:

- Controllers for handling user requests
- Services for business logic and AI calls
- Models for database relationships
- Commands for scheduled tasks
- Blade files for user interface pages
- CSS and JavaScript files for frontend behavior and styling

---

## Important Notes

- The `.env` file should not be uploaded to GitHub.
- Real API keys should never be committed to the repository.
- The `vendor/` folder is not included and should be installed using `composer install`.
- The `node_modules/` folder is not included and should be installed using `npm install`.
- If the project is shared as a ZIP file, dependencies must be installed again.
- The database must be configured before running migrations.
- AI features require valid OpenAI API keys.

---

## License

This project is licensed under the MIT License.

---

## Contributors

This project was developed as a graduation project by:

- Leen Alhazmi
- Alya Alharthi
- Shahad Musallam Alqurashi
- Wajen Naif Almatrafi

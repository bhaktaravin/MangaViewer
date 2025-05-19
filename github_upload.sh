#!/bin/bash

# GitHub Upload Script
# This script uploads the current project to GitHub on a development branch
# while keeping the main branch as "read-only"

# Configuration - Change these variables as needed
GITHUB_USERNAME="bhaktaravin"
REPO_NAME="MangaViewer"
DEV_BRANCH="development"
COMMIT_MESSAGE="Enhance MangaView with MyAnimeList integration:
- Fix cover image display issues
- Add automatic chapter fetching from MyAnimeList
- Update manga metadata (description, author, status)
- Add publication date information
- Improve error handling and fallbacks"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Function to display error and exit
error_exit() {
    echo -e "${RED}ERROR: $1${NC}" >&2
    exit 1
}

# Function to display success message
success_message() {
    echo -e "${GREEN}$1${NC}"
}

# Function to display warning message
warning_message() {
    echo -e "${YELLOW}$1${NC}"
}

# Check if git is installed
if ! command -v git &> /dev/null; then
    error_exit "Git is not installed. Please install git and try again."
fi

# Check if GitHub username is provided
if [ -z "$GITHUB_USERNAME" ]; then
    read -p "Enter your GitHub username: " GITHUB_USERNAME
    if [ -z "$GITHUB_USERNAME" ]; then
        error_exit "GitHub username is required."
    fi
fi

# Check if we're in a git repository
if [ ! -d ".git" ]; then
    warning_message "Git repository not found. Initializing a new repository..."
    git init || error_exit "Failed to initialize git repository."
    success_message "Git repository initialized."
else
    warning_message "Git repository already exists."
fi

# Check if the remote repository exists
if ! git remote get-url origin &> /dev/null; then
    warning_message "Remote 'origin' not found. Adding remote..."
    git remote add origin "https://github.com/$GITHUB_USERNAME/$REPO_NAME.git" || error_exit "Failed to add remote."
    success_message "Remote 'origin' added."
else
    warning_message "Remote 'origin' already exists."
fi

# Create .gitignore if it doesn't exist
if [ ! -f ".gitignore" ]; then
    warning_message "Creating .gitignore file..."
    cat > .gitignore << EOL
/node_modules
/public/hot
/public/storage
/storage/*.key
/vendor
.env
.env.backup
.phpunit.result.cache
docker-compose.override.yml
Homestead.json
Homestead.yaml
npm-debug.log
yarn-error.log
/.idea
/.vscode
EOL
    success_message ".gitignore file created."
fi

# Stage all files
git add . || error_exit "Failed to stage files."
success_message "Files staged for commit."

# Commit changes
git commit -m "$COMMIT_MESSAGE" || error_exit "Failed to commit changes."
success_message "Changes committed."

# Create the development branch if it doesn't exist
if ! git show-ref --verify --quiet refs/heads/$DEV_BRANCH; then
    git checkout -b $DEV_BRANCH || error_exit "Failed to create $DEV_BRANCH branch."
    success_message "Created and switched to $DEV_BRANCH branch."
else
    git checkout $DEV_BRANCH || error_exit "Failed to switch to $DEV_BRANCH branch."
    success_message "Switched to $DEV_BRANCH branch."
fi

# Try to create the repository on GitHub using the GitHub CLI if available
if command -v gh &> /dev/null; then
    warning_message "Attempting to create GitHub repository using GitHub CLI..."
    gh repo create "$REPO_NAME" --private --source=. --remote=origin || warning_message "Repository may already exist or gh CLI failed."
else
    warning_message "GitHub CLI not found. You may need to manually create the repository on GitHub."
    warning_message "Visit https://github.com/new to create a new repository named '$REPO_NAME'."
    read -p "Press Enter to continue after creating the repository..."
fi

# Push to the development branch
warning_message "Pushing to $DEV_BRANCH branch..."
git push -u origin $DEV_BRANCH || error_exit "Failed to push to $DEV_BRANCH branch."
success_message "Successfully pushed to $DEV_BRANCH branch."

# Create an empty main branch
warning_message "Checking if main branch exists..."
if ! git show-ref --verify --quiet refs/heads/main; then
    warning_message "Creating empty main branch..."
    git checkout --orphan main || error_exit "Failed to create main branch."
    git rm -rf . || warning_message "No files to remove from main branch."

    # Create a README.md file for the main branch
    cat > README.md << EOL
# $REPO_NAME

This is the main branch of the $REPO_NAME repository.

## Important Notice

This branch is considered **read-only**. All development work should be done on the \`$DEV_BRANCH\` branch.

To check out the development branch:

\`\`\`bash
git checkout $DEV_BRANCH
\`\`\`

## About

MangaView is a Laravel-based web application that combines user authentication with a personal manga reader. The project uses SQLite for database storage and Laravel Breeze for authentication.
EOL

    # Commit the README to the main branch
    git add README.md || error_exit "Failed to add README.md to main branch."
    git commit -m "Add README.md to main branch" || error_exit "Failed to commit README.md to main branch."

    # Push the main branch
    warning_message "Pushing to main branch..."
    git push -u origin main || error_exit "Failed to push to main branch."
    success_message "Successfully pushed to main branch."
else
    success_message "Main branch already exists. Skipping main branch creation."
fi

# Switch back to the development branch
git checkout $DEV_BRANCH || error_exit "Failed to switch back to $DEV_BRANCH branch."
success_message "Switched back to $DEV_BRANCH branch."

# Final success message
success_message "==============================================="
success_message "Repository setup complete!"
success_message "Main branch is now set up as read-only with just a README.md file."
success_message "All your code is on the $DEV_BRANCH branch."
success_message "Repository URL: https://github.com/$GITHUB_USERNAME/$REPO_NAME"
success_message "==============================================="

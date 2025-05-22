#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Starting database setup script...${NC}"

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo -e "${RED}Error: This script must be run from the Laravel project root directory.${NC}"
    exit 1
fi

# Run the database setup PHP script
echo -e "${YELLOW}Running database setup PHP script...${NC}"
php database_setup.php

# Check if the script ran successfully
if [ $? -eq 0 ]; then
    echo -e "${GREEN}Database setup completed successfully.${NC}"
else
    echo -e "${RED}Database setup encountered errors. Check the logs for details.${NC}"
    exit 1
fi

# Run Laravel migrations to ensure all tables are up to date
echo -e "${YELLOW}Running Laravel migrations...${NC}"
php artisan migrate --force

# Check if migrations ran successfully
if [ $? -eq 0 ]; then
    echo -e "${GREEN}Migrations completed successfully.${NC}"
else
    echo -e "${RED}Migrations encountered errors. Check the logs for details.${NC}"
    exit 1
fi

echo -e "${GREEN}Database setup and migrations completed successfully!${NC}"
echo -e "${YELLOW}Your MangaView application should now be ready to use.${NC}"

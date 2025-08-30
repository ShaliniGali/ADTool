FROM node:18-alpine

EXPOSE 3000

WORKDIR /app

# Copy package files first for better caching
COPY package*.json ./
COPY assets/package*.json ./assets/
COPY sso-tile-app/package*.json ./sso-tile-app/

# Install dependencies with force flag to handle any conflicts
RUN npm install --force

# Copy all source files
COPY . .

# Try to build, but don't fail if it doesn't work
RUN npm run build || echo "Build failed, continuing with existing files..."

# Ensure node_modules permissions are correct
RUN chown -R node:node /app

WORKDIR /app

# Switch to non-root user
USER node

ENTRYPOINT ["node", "server.js"]

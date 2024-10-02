# Use the official PostgreSQL image from Docker Hub
FROM postgres:13

# Set environment variables for the default database, user, and password
ENV POSTGRES_DB=app
ENV POSTGRES_USER=app
ENV POSTGRES_PASSWORD=tinyCRM_db

# Optional: Copy any initialization SQL scripts to the docker-entrypoint-initdb.d folder
# These scripts will be automatically executed when the container starts for the first time
# COPY ./init.sql /docker-entrypoint-initdb.d/

# Expose the default PostgreSQL port
EXPOSE 5432

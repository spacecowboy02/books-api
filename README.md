# Project Setup Instructions

To deploy this project, you need to have Docker and Docker Compose installed on your machine.

## Steps to Deploy the Project

1. **Build and Start the Containers**

   First, build and start the containers by executing the following command:

   ```bash
   docker-compose up --build
   
2. **Access the PHP Container**

   Once all containers are up and running, and you have confirmed their status, access the PHP container with the command:

   ```bash
   docker-compose exec php sh
   
3. **Run Migrations**

   Inside the PHP container, run the database migrations:

   ```bash
   php bin/console doctrine:migrations:migrate

4. **Set Permissions for Uploads Directory**

   Inside the PHP container, grant the necessary permissions to the folder for saving images:

   ```bash
   chmod -R 777 public/uploads

5. **Access the API Documentation**

   Congratulations! Now, you can navigate to http://localhost:8080/api/docs and start using the API.

Feel free to reach out if you encounter any issues during the setup process.
# University councils system

## Prerequisites

-   Make sure you have Docker installed and running on your machine.
-   Make sure to have WSL 2 installed on your machine.

 **1. Clone the Repository**

     -  Run the following command to clone the repository
         > git clone https://github.com/pksystemsadmin/uni_councils.git
     -  cd uni_councils

 **2. Set Up Environment Variables**

-   In the project root directory, create a new .env file by duplicating the .env.example file
    > cp .env.example .env
-   Open the .env file and update the necessary environment variables such as database credentials, app key, etc., according to your local setup

 **3. Install Composer Dependencies**

-   While still in the project root directory, run the following command to install the Composer dependencies:
    > composer install

 **4. Generate Application Key**

-   Run the following command to generate an application key:
    > php artisan key:generate

 **5. Run Database Migrations**

-   Run the following command to run the database migrations and seed the database:
    > php artisan migrate --seed

 **6. Start Docker Containers with Sail**

-   Run the following command to start the Docker containers using Sail:

    > ./vendor/bin/sail up -d

    _This command will start the Docker containers defined in the docker-compose.yml file_

 **7. Access the Application**

-   Open your web browser.
-   Visit http://localhost to access the Laravel application.

 **8. Access Filament Admin Panel**

-   To access the Filament admin panel, visit http://localhost/app.
-   Log in using the credentials you provided during the seeding process.

    | Role          |          Email          | Password |
    | ------------- | :---------------------: | -------: |
    | Super Admin   |     super@gmail.com     | password |
    | System Admin  | system_admin@gmail.com  | password |
    | Faculty Admin | faculty_admin@gmail.com | password |
    | User          |     user@gmail.com      | password |

#### Customizing the Login page :

-   open this path in your IDE

    > lang\vendor\filament-panels\en\pages\auth\login.php

-   copy this and overwrite the existing code :

```php
<?php

return [

    'title' => 'Log In',

    'heading' => 'Log In',

    'actions' => [

        'register' => [
            'before' => 'or',
            'label' => 'sign up for an account',
        ],

        'request_password_reset' => [
            'label' => 'Forgotten your password?',
        ],

    ],

    'form' => [

        'email' => [
            'label' => 'Email address',
        ],

        'password' => [
            'label' => 'Password',
        ],

        'remember' => [
            'label' => 'Remember me',
        ],

        'actions' => [

            'authenticate' => [
                'label' => 'Log In',
            ],
        ],
    ],
    'messages' => [

        'failed' => 'Invalid data.',
    ],

    'notifications' => [

        'throttled' => [
            'title' => 'Too many login attempts',
            'body' => 'Please try again in :seconds seconds.',
        ],
    ],
];
```

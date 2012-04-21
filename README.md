# Welcome to Cumula

## Code less. Make more.

Cumula is a web development framework for building applications directly on top of APIs. This means that you can build applications faster and with less code.

## Cumula Installation Instructions

### Step 1: Get the code

Getting the code from GitHub is easiest:

```
git clone git://github.com/Cumula/cumula-core.git
```

Alternatively, you can download the latest tarball from https://github.com/Cumula/cumula-core/tarball/master

Next, untar the file using

```
tar -xzf /path/to/cumula/installer.tar.bz
```

### Step 2: Run the installer

Navigate into the `cumula-core` directory and run the command

```
> ./install myAppName -base-dir=../
```

Where `myAppName` is the name of the application (folder) you want Cumula installed to and base-dir is the directory you want to create the project in.

### Step 3: Setup dependencies with composer

In your myAppName directory, run:

` > php composer.phar install `

### Step 3.1: Optionally, start tracking your project as a git versioned project

```
    > git init
	> git add .
	> git commit -m "initial project files"
```

### Step 4: Setup your Web Server

Setup your web server to serve the app's public directory as your DocumentRoot. For example, Apache with a VirtualHost would look like:

```
<VirtualHost *:80>
    ServerName myapp.dev

    VirtualDocumentRoot "/path/to/myAppName/app/public"
    <Directory "/path/to/myAppName/app/public">
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Order allow,deny
        Allow from all
    </Directory>
</VirtualHost>
```

### Step 5: Run the GUI Setup

Finally, navigate to your web server (usually, http://localhost) and follow the setup instructions.

## Resources

1. *Github*: https://github.com/Cumula/cumula-core
1. *Documentation Wiki*: https://github.com/Cumula/cumula-core/wiki
1. *Discussion Group*: https://groups.google.com/forum/cumula
1. *Project Page*: http://cumula.org/

## Community

1. *Cumula campfire room*: https://seabourneconsulting.campfirenow.com/room/411383
1. *Google Groups*: https://groups.google.com/forum/#!forum/cumula

<h1 align="center">
    <br>
    <a href="https://github.com/alextselegidis/clientverse">
        <img src="https://raw.githubusercontent.com/alextselegidis/clientverse/main/logo.png" alt="Clientverse" width="150">
    </a>
    <br>
    Clientverse
    <br>
</h1>

<br>

<h4 align="center">
    A simple Bookmark Manager App that can be installed on your server. 
</h4>

<p align="center">
  <img alt="GitHub" src="https://img.shields.io/github/license/alextselegidis/clientverse?style=for-the-badge">
  <img alt="GitHub release (latest by date)" src="https://img.shields.io/github/v/release/alextselegidis/clientverse?style=for-the-badge">
  <img alt="GitHub All Releases" src="https://img.shields.io/github/downloads/alextselegidis/clientverse/total?style=for-the-badge">
</p>

<p align="center">
  <a href="#about">About</a> •
  <a href="#features">Features</a> •
  <a href="#setup">Setup</a> •
  <a href="#installation">Installation</a> •
  <a href="#license">License</a>
</p>

![screenshot](screenshot.png)

## About

**Clientverse** is a bookmark manager application, designed for simplicity and efficiency. Everyone knows that running 
any software to production requires a lot of care so that everything works according to the plan. With Clientverse you 
can spectate the current health state of your systems in real time.

## Features

The application will allow you to manage and organize your bookmark links.

## Setup

To clone and run this application, you'll need Docker installed on your computer. From your command line:

```bash
# Clone this repository
$ git clone https://github.com/alextselegidis/clientverse.git

# Go into the repository
$ cd clientverse

# Install dependencies
$ docker compose up -d
```

Then you can SSH into the PHP-FPM container and install the dependencies with `composer install`. 

Note: the current setup works with Windows and WSL & Docker.

You can build the files by running `bash build.sh`. This command will bundle everything to a `build.zip` archive.

## Installation

You will need to perform the following steps to install the application on your server:

* Make sure that your server has Apache/Nginx, PHP (8.2+) and MySQL installed.
* Create a new database (or use an existing one).
* Copy the "clientverse" source folder on your server.
* Make sure that the "storage" directory is writable.
* Rename the ".env.example" file to ".env" and update its contents based on your environment.
* Open the browser on the Clientverse URL and follow the installation guide.

That's it! You can now use Clientverse at your will.

You will find the latest release at [github.com/alextselegidis/clientverse](github.com/alextselegidis/clientverse).
You can also report problems on the [issues page](https://github.com/alextselegidis/clientverse/issues)
and help the development progress.

## License

Code Licensed Under [GPL v3.0](https://www.gnu.org/licenses/gpl-3.0.en.html) | Content Under [CC BY 3.0](https://creativecommons.org/licenses/by/3.0/)

---

Website [alextselegidis.com](https://alextselegidis.com) &nbsp;&middot;&nbsp;
GitHub [alextselegidis](https://github.com/alextselegidis) &nbsp;&middot;&nbsp;
Twitter [@alextselegidis](https://twitter.com/AlexTselegidis)

###### More Projects On Github
###### ⇾ [Plainpad &middot; Self Hosted Note Taking App](https://github.com/alextselegidis/plainpad)
###### ⇾ [Easy!Appointments &middot; Online Appointment Scheduler](https://github.com/alextselegidis/easyappointments)
###### ⇾ [Integravy &middot; Service Orchestration At Your Fingertips](https://github.com/alextselegidis/integravy)


General changes:

- The pagination must have the colors of the current theme and noth teh greenish colors of the old design
- The table footer needs ome bottom margin that matches the top margin of the table footer
- 

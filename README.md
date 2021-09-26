# Quiver

A "clone" of <a href="https://fr.quora.com/">Quora</a>  website.

This project is for training purpose only. Don't use real infos here ! 

<!-- The website is available here : https://spacetrip-app.herokuapp.com/ -->

# Requirements

- Symfony > 5.0.0
- PHP > 7.2
- Apache server
- MySQL or PostGRESQL

# How to run

1) Clone the project on your local repository

```bash
git clone git@github.com:AlexandreRavichandran/Quiver.git`
```
2) go to .env file, remove the # and change the DATABASE_URL depending on your database software

3) Create the database with either Symfony CLI (if you have it installed ) or basic php console commands.

```bash
symfony console doctrine:database:create
```
or
```bash
php bin/console doctrine:database:create
```

4) Make all migrations to the database to create and update all fields on the database

```bash
symfony console doctrine:migrations:migrate
```

or 

```bash
php bin/console doctrine:migrations:migrate
```

5) (OPTIONNAL) Create fixtures to add some fake data so that you can explore all features of the website

```bash
symfony console doctrine:fixtures:load
```

or

```bash
php bin/console doctrine:fixtures:load
```

6) open the php server

```bash
symfony serve -d
```

or

```bash
php -S 0.0.0.0:8080 -tpublic
```


# Features

You can see here all features that Quiver provides : 

- Ask a question and answer to a question
- Add comment and sub-comment
- Creating space and relate question to spaces
- Subscription system between users and between user and spaces
- Editing profile ('profile picture and profile informations')


# Origin of the project
This project is the second project that I made with Symfony

I decided to make a website to work my skill about database relations, and API requests. I decided to do a "clone" of the <a href="https://fr.quora.com/">Quora</a> website. Quora is a forum website where users can ask a question about a subject, and answer to another user's question. There is also a comment section feature, subscription system, and spaces section. I have implemented all theses features on my project.

In this project, I paid attention to respect Symfony coding standard and symfony syntax, and also documentations on my controllers methods. I tried to respect KISS too.

# Pre-commit
This is a console application which adds git pre-commit hook and gives you an ability to run custom programs on this git event.

## Installation
The easiest way to install this library is with [Composer](https://getcomposer.org/) using the following command:
```
$ composer require --dev redcode/precommit
```

## Configuration
In the root folder of your project you need to add a .pre-commit.[yml](https://en.wikipedia.org/wiki/YAML) file with custom programs configuration.
```shell
active:
  - php-code-style # name of active custom program
hooks:
  php-code-style: # key(name) of active custom program
    description: "PHP Code Style" # description, which will be shown on process run
    class: RedCode\GitHook\Process\PhpCsProcess # custom class which extends AbstractGitHookProcess
    script: "bin/php-cs-fixer fix --diff --dry-run %relativeFile%" # custom script to execute
    file_types: "php,phtml" # files types which will be checked by pre commit hook // if empty - all files
    paths: # prjecect paths which will be checked by pre commit hook // if empty - all root directory
        - src/My/Custom/Code
        - src/My/Custom/Lib
```
You're able to add so many custom programs as you want.

Pre-commit is already contains [Php-Cs-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) in the box.
So you can just activate it using following lines:
```shell
active:
  - php-code-style
```
If you need to extend custom program with some options, you can do it easily in your configuration file:
```shell
active:
  - php-code-style
hooks:
  php-code-style:
    paths:
        - src/My/Custom/Code
        - src/My/Custom/Lib
```
Be aware that fields `description` and `class`|`script` are required.
Configuration node `class` has a higher priority than `script`, but one of them must be specified.

## How does it work?
After you have successfully installed this program and created configuration file, you need to activate it in your local git repository.
Pre-commit hook can be activated in two ways:
* **Manually**: use command `./bin/pre-commit install`
* **Composer event** (*RECOMMENDED*):

  Add to your composer.json file following section:
  ```json
  "scripts": {
      "post-update-cmd": "RedCode\\GitHook\\ComposerListener::postUpdate",
      "post-install-cmd": "RedCode\\GitHook\\ComposerListener::postInstall"
  },
  ```
  This way will add a pre-commit hook to all your developers after they do `composer install` or `update` commands.

## What's next?
Just try to commit something and if your custom script says that file is *NOT* good, you will see output and commit process will be suspended.

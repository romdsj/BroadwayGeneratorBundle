[![Build Status](https://travis-ci.org/RomainDeSaJardim/BroadwayGeneratorBundle.svg?branch=master)](https://travis-ci.org/RomainDeSaJardim/BroadwayGeneratorBundle)
[![codecov](https://codecov.io/gh/RomainDeSaJardim/BroadwayGeneratorBundle/branch/master/graph/badge.svg)](https://codecov.io/gh/RomainDeSaJardim/BroadwayGeneratorBundle)

# BroadwayGeneratorBundle
This bundle gives some symfony commands to generate Commands, Events, ReadModels, and more, for Broadway.

This bundle is highly inspired by [SensioGeneratorBundle](https://github.com/sensiolabs/SensioGeneratorBundle)
 
 ## Installation
 
 Install it with composer
 
 ```composer require romaindesajardim/broadway-generator-bundle```
 
 and add it to your `AppKernel.php`
 
 ```new RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\BroadwayGeneratorBundle(),```
 
 ## Features
 
 Actually there is only one command available. More will come soon ...
 
 ### Broadway Commands
 
 This command generate a Broadway command and implements the handle method in the Broadway Command Handler automatically. You have just to add your parameters and code the handler.
 
 ```php bin/console rdsj:broadway:generate-command```
 
 :warning: If you use Symfony 2, use `app/console` instead of `bin/console`
 
 This command need 3 inputs :
 
 * `bundle` _(The bundle name where the Broadway Command will be generate)_
 * `name` _(The name of the Broadway Command you want)_
 * `command-handler` Optionnal _(The Command Handler's service id where the Broadway Command will be handle)_
 
 By default the command is run in the interactive mode and asks questions to determine values of thoose inputs
 
 But if you want, you can run the command in a non-interactive mode and providing the needed inputs
 
 ```php bin/console rdsj:broadway:generate-command --no-interaction FooBarBundle FooBar [foo.bar-handler]```
 
 #### Exemple
 
 Imagine I have my Broadway Command Handler `FooBarBundle\FooBarCommandHandler` linked by a Symfony service :
 
 ```
<service id="foo.bar.command_handler" class="FooBarBundle\FooBarCommandHandler">
    <tag name="command_handler"/>
</service>
```

And I want to create a Broadway Command `FooBar` in the same bundle as my Command Handler `FooBarBundle`

So in a non interactive mode I have to launch this command :

`php bin/console rdsj:broadway:generate-command --no-interaction FooBarBundle FooBar foo.bar.command_handler`

The result will be :

A new file `Command\FooBarCommand.php`

```
<?php

namespace FooBarBundle\Command;


class FooBarCommand
{
    private $uuid;

    public function __construct($uuid)
    {
        $this->uuid = $uuid;
    }

    public function getUUID()
    {
        return $this->uuid;
    }
}
```

And a new handle method a the end of my Command Handler `FooBarCommandHandler.php`

```
public function handleFooBar(FooBarCommand $command)
{
    // @TODO Insert your code here
}
```

## TODO

- [ ] Add a command to generate Broadway Event
- [ ] Add a command to generate Broadway ReadModel
- [ ] Add a command to generate Broadway Command Handler
- [ ] Generate Command handler automatically if it doesn't exist on Broadway Command creation
- [ ] Add fields to Broadway Command to add fields and implements getters

## Reporting an issue or a feature request

You feel free to report a new issue or a feature requests if it doesn't already exist or is in the Todo list (for feature request)
 
## Contribute

You feel free to open pull request